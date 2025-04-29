<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search, Plus, ArrowDown, ArrowUp } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

// Props definition
const props = defineProps<{
  tasks?: {
    data: Array<{
      id: number;
      title: string;
      description: string | null;
      status: 'to-do' | 'in-progress' | 'done';
      image_path: string | null;
      is_published: boolean;
      created_at: string;
    }>;
    meta: {
      current_page: number;
      from: number;
      last_page: number;
      links: Array<{
        url: string | null;
        label: string;
        active: boolean;
      }>;
      path: string;
      per_page: number;
      to: number;
      total: number;
    };
  };
  filters?: {
    status: string | null;
    search: string | null;
    order_by: string;
    order_direction: string;
    per_page: number;
  };
}>();

// Create fallbacks for props
const tasks = props.tasks || { 
  data: [], 
  meta: {
    current_page: 1,
    from: 0,
    last_page: 1,
    links: [],
    path: '',
    per_page: 10,
    to: 0,
    total: 0
  }
};

// Add full dump of received data
console.log('FULL RECEIVED DATA:', {
  propsAvailable: !!props,
  propsTasksAvailable: !!props?.tasks,
  rawProps: JSON.parse(JSON.stringify(props || {})),
  rawTasks: JSON.parse(JSON.stringify(tasks || {}))
});

// Handle both structures - when meta is at the root level or inside 'meta' property
const metaData = tasks.meta || tasks;
const paginationFrom = metaData.from || 1;
const paginationTo = metaData.to || (tasks.data?.length || 0);
const paginationTotal = metaData.total || (tasks.data?.length || 0);
const currentPage = metaData.current_page || 1;
const lastPage = metaData.last_page || 1;

const filters = props.filters || {
  status: null,
  search: null,
  order_by: 'created_at',
  order_direction: 'desc',
  per_page: 10
};

// Reactive search query
const search = ref(filters.search || '');
const status = ref(filters.status || '');
const orderBy = ref(filters.order_by || 'created_at');
const orderDirection = ref(filters.order_direction || 'desc');
const perPage = ref(filters.per_page || 10);

// Debounced search
const debouncedSearch = debounce(() => {
  applyFilters();
}, 300);

// Watch for search changes
watch(search, () => {
  debouncedSearch();
});

// Watch for all filter changes to apply them
watch([status, orderBy, orderDirection, perPage], () => {
  console.log('Filter changed, applying...');
  applyFilters();
}, { deep: true });

// Apply filters
function applyFilters() {
  // Log the filter values for debugging
  console.log('Applying filters:', {
    status: status.value,
    search: search.value,
    order_by: orderBy.value,
    order_direction: orderDirection.value,
    per_page: perPage.value,
  });

  // Ensure we're handling empty values correctly
  const params: Record<string, string | number | null> = {};
  
  // Only add non-empty values to params (except for required ones)
  if (status.value) params.status = status.value;
  if (search.value) params.search = search.value;
  
  // These are always required
  params.order_by = orderBy.value;
  params.order_direction = orderDirection.value;
  params.per_page = perPage.value;
  
  console.log('Sending params to server:', params);
  console.log('URL will be:', route('tasks.index', params));

  // Force a fresh server request with preserveScroll to keep position
  router.get(
    route('tasks.index'),
    params,
    {
      preserveState: false, // Don't preserve state to force fresh data
      preserveScroll: true, // Keep scroll position
      replace: true,
      onSuccess: () => {
        console.log('Filter applied successfully, data refreshed');
      },
      onError: (errors) => {
        console.error('Error applying filter:', errors);
      }
    }
  );
}

// Toggle sort direction
function toggleSort(column: string) {
  if (orderBy.value === column) {
    orderDirection.value = orderDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    orderBy.value = column;
    orderDirection.value = 'asc';
  }
  applyFilters();
}

// Update task status
function updateTaskStatus(taskId: number, newStatus: string) {
  // Send the PATCH request
  router.patch(
    route('tasks.update-status', taskId),
    { status: newStatus },
    {
      preserveScroll: true,
      preserveState: false, // Force Inertia to update the state
      onSuccess: () => {
        // No need to reload, Inertia will handle the update
      },
      onError: (errors) => {
        console.error('Error updating status:', errors);
      }
    }
  );
}

// Toggle task publication status
function togglePublished(taskId: number) {
  // Send the PATCH request
  router.patch(
    route('tasks.toggle-published', taskId),
    {},
    {
      preserveScroll: true,
      preserveState: false, // Force Inertia to update the state
      onSuccess: () => {
        // No need to reload, Inertia will handle the update
      },
      onError: (errors) => {
        console.error('Error toggling publication status:', errors);
      }
    }
  );
}

// Get status badge class
function getStatusClass(status: string): string {
  switch (status) {
    case 'to-do':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
    case 'in-progress':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    case 'done':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
  }
}

// Handle image loading errors
function handleImageError(e: Event) {
  const imgElement = e.target as HTMLImageElement;
  imgElement.src = '/images/placeholder.png'; // Fallback to placeholder
  imgElement.classList.add('image-error');
  
  // Set a simple fallback if placeholder is not available
  imgElement.onerror = function() {
    imgElement.style.display = 'none';
    const parent = imgElement.parentElement;
    if (parent) {
      const errorSpan = document.createElement('span');
      errorSpan.className = 'text-xs text-gray-400';
      errorSpan.innerText = 'Image unavailable';
      parent.appendChild(errorSpan);
    }
  };
}


// Function to change page
function changePage(page: number) {
  // Validate the page number
  if (page < 1) page = 1;
  if (page > lastPage) page = lastPage;
  
  // Only change page if it's different from the current page
  if (page === currentPage) return;
  
  console.log(`Changing to page ${page} of ${lastPage}`);
  
  // Create params with current filters plus page
  const params: Record<string, string | number | null> = {};
  if (status.value) params.status = status.value;
  if (search.value) params.search = search.value;
  params.order_by = orderBy.value;
  params.order_direction = orderDirection.value;
  params.per_page = perPage.value;
  params.page = page;
  
  // Navigate using router to maintain state
  router.get(
    route('tasks.index'),
    params,
    {
      preserveState: false,
      preserveScroll: true,
      replace: true,
      onSuccess: () => {
        console.log(`Successfully changed to page ${page}`);
      }
    }
  );
}

// Use manual page calculation if needed
const manualTotalPages = getManualTotalPages();
const totalPages = metaData.last_page || 1; // Use the backend's last_page value directly

// Debug pagination values
console.log('Pagination debugging:', {
  currentPage,
  backendLastPage: metaData.last_page,
  manualTotalPages,
  totalPages,
  metaTotal: metaData.total,
  perPageValue: perPage.value,
  dataLength: tasks.data.length
});

// Function to calculate total pages
function calculateTotalPages() {
  const metaTotal = metaData.total || 0;
  const actualTotal = Math.max(metaTotal, tasks.data.length);
  const perPage = metaData.per_page || 10;
  const calculatedPages = Math.max(1, Math.ceil(actualTotal / perPage));
  
  // Log to debug
  console.log(`Pagination data:`, {
    metaTotal,
    actualTotal,
    dataLength: tasks.data.length,
    perPage,
    calculatedPages,
    metaLastPage: metaData.last_page || 1,
    currentPage: metaData.current_page || 1
  });
  
  // Return nothing as this is just for debugging
  return '';
}

// Add this right after tasks and filters variables are defined
// console.log('Tasks data received from backend:', JSON.parse(JSON.stringify(tasks)));
// console.log('Meta data structure:', JSON.stringify(tasks.meta, null, 2));
// console.log('Props structure:', JSON.stringify(props, null, 2));

// Function to manually count pages
function getManualTotalPages() {
  // Get current data from the table
  const visibleCount = tasks.data.length;
  const currentPerPage = Number(perPage.value);
  const apiReportedTotal = metaData.total || 0;
  const apiReportedPages = metaData.last_page || 1;
  
  // For debugging
  console.log('Manual pagination calculation:', {
    visibleCount,
    currentPerPage,
    apiReportedTotal,
    apiReportedPages,
    // Try to detect if we're on the last page
    isLastPage: apiReportedTotal <= currentPerPage || visibleCount < currentPerPage
  });
  
  // If we have fewer items than the per_page setting, we're likely on the last page
  // OR if the API reported total is less than or equal to per_page
  const isLastPage = apiReportedTotal <= currentPerPage || visibleCount < currentPerPage;
  
  // If we're on the last page and have items, we can estimate total
  if (isLastPage && visibleCount > 0) {
    // We're on the last page, so we can calculate total from current page and visible count
    const currentPageNum = Number(metaData.current_page || 1);
    const estimatedTotal = (currentPageNum - 1) * currentPerPage + visibleCount;
    console.log('Estimated total from last page:', estimatedTotal);
    return Math.ceil(estimatedTotal / currentPerPage);
  }
  
  // If API reported more than one page, trust it
  if (apiReportedPages > 1) {
    return apiReportedPages;
  }
  
  // If we have a full page of data, assume there might be more
  if (visibleCount >= currentPerPage) {
    return 2; // Assume at least 2 pages if we have a full page
  }
  
  // Default fallback
  return Math.max(1, apiReportedPages);
}

// Function to force navigation to a specific page
function forceGoToPage(page: number) {
  console.log(`Forcing navigation to page ${page}`);
  const params: Record<string, string | number | null> = {};
  if (status.value) params.status = status.value;
  if (search.value) params.search = search.value;
  params.order_by = orderBy.value;
  params.order_direction = orderDirection.value;
  params.per_page = perPage.value;
  params.page = page;
  
  router.get(
    route('tasks.index'),
    params,
    {
      preserveState: false,
      preserveScroll: true,
      replace: true
    }
  );
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Tasks',
    href: route('tasks.index'),
  },
];
</script>

<template>
  <Head title="Tasks" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Header with filters -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold">Task Management</h1>
        <Link
          :href="route('tasks.create')"
          class="inline-flex items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
          <Plus class="h-4 w-4" />
          Add Task
        </Link>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Search -->
        <div class="relative">
          <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
          <input
            v-model="search"
            type="text"
            placeholder="Search tasks..."
            class="w-full rounded-md border border-gray-300 py-2 pl-10 pr-4 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-gray-600 dark:bg-sidebar dark:text-white dark:placeholder-gray-400"
          />
        </div>

        <!-- Status Filter -->
        <div>
          <select
            v-model="status"
            @change="applyFilters()"
            class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-gray-600 dark:bg-sidebar dark:text-white"
          >
            <option value="">All Statuses</option>
            <option value="to-do">To Do</option>
            <option value="in-progress">In Progress</option>
            <option value="done">Done</option>
          </select>
        </div>

        <!-- Debug info (remove in production) -->
        <div class="hidden">
          <h4 class="font-bold mb-1">Debug Info:</h4>
          <p>Tasks in table: {{ tasks.data.length }}</p>
          <p>API current page: {{ metaData.current_page || 1 }}</p>
          <p>API last page: {{ metaData.last_page || 1 }}</p>
          <p>API total: {{ metaData.total || 0 }}</p>
          <p>Per page setting: {{ perPage }}</p>
          <p><strong>Manual total pages: {{ manualTotalPages }}</strong></p>
          <p>Filter: {{ status || 'none' }}</p>
          <p>URL: {{ route('tasks.index', { status: status || null }) }}</p>
          
          <!-- Force navigation controls -->
          <div v-if="tasks.data.length >= Number(perPage.value)" class="mt-2 text-center">
            <button 
              @click="forceGoToPage(2)"
              class="px-3 py-1 rounded-md border border-gray-300 bg-blue-50 text-sm text-primary"
            >
              Try Page 2 (Force)
            </button>
          </div>
        </div>

        <!-- Per Page -->
        <div>
          <select
            v-model="perPage"
            @change="applyFilters"
            class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-gray-600 dark:bg-sidebar dark:text-white"
          >
            <option :value="10">10 per page</option>
            <option :value="20">20 per page</option>
            <option :value="50">50 per page</option>
            <option :value="100">100 per page</option>
          </select>
        </div>
      </div>

      <!-- Tasks table -->
      <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">
                <div class="flex justify-center">
                  <button 
                    @click="toggleSort('title')" 
                    class="flex items-center font-bold text-primary hover:text-primary-dark transition-colors"
                  >
                    Title
                    <span class="ml-1">
                      <ArrowUp v-if="orderBy === 'title' && orderDirection === 'asc'" class="h-4 w-4 text-primary" />
                      <ArrowDown v-if="orderBy === 'title' && orderDirection === 'desc'" class="h-4 w-4 text-primary" />
                      <ArrowDown v-if="orderBy !== 'title'" class="h-4 w-4 text-gray-300" />
                    </span>
                  </button>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-center">Image</th>
              <th scope="col" class="px-6 py-3 text-center">Status</th>
              <th scope="col" class="px-6 py-3 text-center">
                <div class="flex justify-center">
                  <button 
                    @click="toggleSort('created_at')" 
                    class="inline-flex items-center font-bold text-primary hover:text-primary-dark transition-colors"
                  >
                    Created
                    <span class="ml-1">
                      <ArrowUp v-if="orderBy === 'created_at' && orderDirection === 'asc'" class="h-4 w-4 text-primary" />
                      <ArrowDown v-if="orderBy === 'created_at' && orderDirection === 'desc'" class="h-4 w-4 text-primary" />
                      <ArrowDown v-if="orderBy !== 'created_at'" class="h-4 w-4 text-gray-300" />
                    </span>
                  </button>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left">Published</th>
              <th scope="col" class="px-6 py-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="task in tasks.data" :key="task.id" class="border-b bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                <Link :href="route('tasks.show', task.id)" class="hover:underline">
                  {{ task.title }}
                </Link>
              </td>
              <td class="px-6 py-4 text-center">
                <div v-if="task.image_path" class="flex justify-center">
                  <img 
                    :src="'/storage/' + task.image_path" 
                    alt="Task Image" 
                    class="h-16 w-16 object-cover rounded-md border border-gray-200 shadow-sm hover:scale-150 transition-transform cursor-zoom-in"
                    @error="handleImageError"
                  />
                </div>
                <div v-else class="flex justify-center text-gray-400">
                  <span class="text-xs">No image</span>
                </div>
              </td>
              <td class="px-6 py-4 text-center">
                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', getStatusClass(task.status)]">
                  {{ task.status.replace(/-/g, ' ') }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                {{ new Date(task.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 text-center">
                <button 
                  @click="togglePublished(task.id)" 
                  :class="[
                    'flex items-center justify-center gap-1 rounded-md px-3 py-1.5 text-xs font-medium transition-all cursor-pointer shadow-sm border',
                    task.is_published 
                      ? 'bg-green-100 text-green-800 border-green-300 hover:bg-green-200 hover:border-green-400 dark:bg-green-900 dark:text-green-300 dark:border-green-800 dark:hover:bg-green-800' 
                      : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200 hover:border-gray-400 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700'
                  ]"
                >
                  <span v-if="task.is_published" class="bg-green-500 dark:bg-green-600 w-2 h-2 rounded-full"></span>
                  <span v-else class="bg-gray-400 dark:bg-gray-500 w-2 h-2 rounded-full"></span>
                  {{ task.is_published ? 'Published' : 'Draft' }}
                </button>
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex justify-center gap-2">
                  <select 
                    :value="task.status"
                    @change="(e) => updateTaskStatus(task.id, (e.target as HTMLSelectElement).value)"
                    class="rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:bg-sidebar dark:text-white"
                  >
                    <option value="to-do">To Do</option>
                    <option value="in-progress">In Progress</option>
                    <option value="done">Done</option>
                  </select>

                  <Link :href="route('tasks.edit', task.id)" class="rounded bg-blue-500 px-2 py-1 text-xs text-white hover:bg-blue-600">
                    Edit
                  </Link>

                  <Link 
                    :href="route('tasks.destroy', task.id)" 
                    method="delete"
                    as="button" 
                    type="button"
                    class="rounded bg-red-500 px-2 py-1 text-xs text-white hover:bg-red-600"
                    onclick="return confirm('Are you sure you want to delete this task?')"
                  >
                    Delete
                  </Link>
                </div>
              </td>
            </tr>
            <tr v-if="tasks.data.length === 0" class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
              <td colspan="6" class="px-6 py-4 text-center">No tasks found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mt-4">
        <div class="text-sm text-gray-700 dark:text-gray-400">
          Showing 
          <span class="font-semibold">{{ paginationFrom }}</span> 
          to 
          <span class="font-semibold">{{ paginationTo }}</span> 
          of 
          <span class="font-semibold">{{ paginationTotal }}</span>
          tasks
          (Page {{ currentPage }} of {{ totalPages }})
        </div>
        
        <!-- Basic pagination (always shown if we have data) -->
        <div class="flex items-center justify-center space-x-1">
          <!-- Calculate total pages (silent call) -->
          <span class="hidden">{{ calculateTotalPages() }}</span>
          
          <!-- First/Previous -->
          <button 
            @click="changePage(1)" 
            :disabled="currentPage <= 1"
            class="px-3 py-1 rounded-md border text-sm" 
            :class="currentPage <= 1 ? 'opacity-50 cursor-not-allowed border-gray-300' : 'border-gray-300 hover:bg-gray-100'"
          >
            &laquo;
          </button>
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage <= 1"
            class="px-3 py-1 rounded-md border text-sm" 
            :class="currentPage <= 1 ? 'opacity-50 cursor-not-allowed border-gray-300' : 'border-gray-300 hover:bg-gray-100'"
          >
            Previous
          </button>
          
          <!-- Page numbers (always show at least page 1) -->
          <div class="flex space-x-1">
            <template v-for="page in Math.min(5, totalPages)" :key="page">
              <button 
                @click="changePage(page)" 
                class="px-3 py-1 rounded-md border text-sm" 
                :class="page === currentPage ? 'bg-primary/10 border-primary text-primary' : 'border-gray-300 hover:bg-gray-100'"
              >
                {{ page }}
              </button>
            </template>
          </div>
          
          <!-- Next/Last -->
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage >= totalPages"
            class="px-3 py-1 rounded-md border text-sm" 
            :class="currentPage >= totalPages ? 'opacity-50 cursor-not-allowed border-gray-300' : 'border-gray-300 hover:bg-gray-100'"
          >
            Next
          </button>
          <button 
            @click="changePage(totalPages)" 
            :disabled="currentPage >= totalPages"
            class="px-3 py-1 rounded-md border text-sm" 
            :class="currentPage >= totalPages ? 'opacity-50 cursor-not-allowed border-gray-300' : 'border-gray-300 hover:bg-gray-100'"
          >
            &raquo;
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 