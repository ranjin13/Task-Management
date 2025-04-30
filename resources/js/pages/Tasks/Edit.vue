<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props for task data
const props = defineProps<{
  task: {
    id: number;
    title: string;
    description: string | null;
    status: 'to-do' | 'in-progress' | 'done';
    image_path: string | null;
    is_published: boolean;
    image_url?: string;
  };
}>();

// Form for task update (without generic type parameter)
const form = useForm({
  title: props.task.title,
  description: props.task.description || '',
  status: props.task.status as 'to-do' | 'in-progress' | 'done',
  is_published: props.task.is_published,
  image: null as File | null,
  _method: 'put',
});

// Preview image
const currentImage = computed(() => {
  if (form.image instanceof File) {
    return URL.createObjectURL(form.image);
  }
  
  if (props.task.image_path) {
    return props.task.image_url || `/storage/${props.task.image_path}`;
  }
  
  return null;
});

// Handle image upload
function handleImageUpload(event: Event) {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files.length > 0) {
    form.image = input.files[0];
  }
}

// Submit form
function submit() {
  form.post(route('tasks.update', props.task.id), {
    preserveScroll: true,
  });
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
  {
    title: 'Edit',
    href: route('tasks.edit', props.task.id),
  },
];
</script>

<template>
  <Head title="Edit Task" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between">
        <h1 class="text-2xl font-bold">Edit Task</h1>
      </div>

      <div class="rounded-lg border bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Title -->
          <div>
            <label for="title" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Title</label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              class="block w-full rounded-lg border border-gray-300 p-2.5 text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              placeholder="Enter task title"
              required
            />
            <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="block w-full rounded-lg border border-gray-300 p-2.5 text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              placeholder="Enter task description"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>

          <!-- Status -->
          <div>
            <label for="status" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Status</label>
            <select
              id="status"
              v-model="form.status"
              class="block w-full rounded-lg border border-gray-300 p-2.5 text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
            >
              <option value="to-do">To Do</option>
              <option value="in-progress">In Progress</option>
              <option value="done">Done</option>
            </select>
            <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
          </div>

          <!-- Image Upload -->
          <div>
            <label for="image" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Task Image</label>
            <input
              id="image"
              type="file"
              accept="image/*"
              @change="handleImageUpload"
              class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:placeholder-gray-400"
            />
            <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</div>
            
            <!-- Image Preview -->
            <div v-if="currentImage" class="mt-4">
              <img :src="currentImage" alt="Task image preview" class="h-40 w-auto rounded-lg object-cover" />
            </div>
          </div>

          <!-- Published Status -->
          <div class="flex items-center">
            <input
              id="is_published"
              v-model="form.is_published"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-primary focus:ring-2 focus:ring-primary dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800"
            />
            <label for="is_published" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Publish this task</label>
            <div v-if="form.errors.is_published" class="mt-1 text-sm text-red-600">{{ form.errors.is_published }}</div>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full rounded-lg bg-primary px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-4 focus:ring-primary/50 disabled:opacity-75"
            >
              {{ form.processing ? 'Updating...' : 'Update Task' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template> 