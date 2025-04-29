<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
  taskId: number;
  subtasks: Array<{
    id: number;
    title: string;
    description: string | null;
    completed: boolean;
    created_at: string;
    updated_at: string;
  }> | null;
}>();

// Use a ref to make it reactive and update it when props change
const subtasksList = ref(props.subtasks || []);

// Update subtasksList when props.subtasks changes
watch(() => props.subtasks, (newSubtasks) => {
  subtasksList.value = newSubtasks || [];
}, { immediate: true });

// Form for toggling subtask completion status
const toggleForm = useForm({
  task_id: props.taskId
});

// Function to toggle subtask completion status
const toggleCompletion = (subtaskId: number) => {
  // Make sure task_id is set before submitting
  toggleForm.task_id = props.taskId;
  
  toggleForm.put(route('subtasks.toggle', subtaskId), {
    preserveScroll: true,
    onSuccess: () => {
      // Force refresh from server to get updated data
      //window.location.reload();
      console.log('Subtask completed successfully');
    }
  });
};

// Form for deleting a subtask
const deleteForm = useForm({
  task_id: props.taskId
});

// Function to delete a subtask
const deleteSubtask = (subtaskId: number) => {
  if (confirm('Are you sure you want to delete this subtask?')) {
    // Make sure task_id is set before submitting
    deleteForm.task_id = props.taskId;
    
    deleteForm.delete(route('subtasks.destroy', subtaskId), {
      preserveScroll: true,
      onSuccess: () => {
        // Force refresh from server to get updated data
        //window.location.reload();
        console.log('Subtask deleted successfully');
      }
    });
  }
};
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Subtasks</h3>
    
    <div v-if="!subtasksList.length" class="rounded-md bg-gray-50 p-4 text-center dark:bg-gray-700">
      <p class="text-gray-500 dark:text-gray-300">No subtasks yet. Add one above.</p>
    </div>
    
    <ul v-else class="divide-y divide-gray-200 rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
      <li v-for="subtask in subtasksList" :key="subtask.id" class="flex items-start justify-between p-4">
        <div class="flex items-start space-x-3">
          <div class="mt-1 flex-shrink-0">
            <input
              type="checkbox"
              :checked="subtask.completed"
              @change="toggleCompletion(subtask.id)"
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
            />
          </div>
          <div>
            <h4 
              class="text-sm font-medium"
              :class="{ 
                'text-gray-900 dark:text-gray-100': !subtask.completed,
                'text-gray-500 dark:text-gray-400 line-through': subtask.completed 
              }"
            >
              {{ subtask.title }}
            </h4>
            <p 
              v-if="subtask.description" 
              class="mt-1 text-sm"
              :class="{
                'text-gray-600 dark:text-gray-300': !subtask.completed,
                'text-gray-400 dark:text-gray-500': subtask.completed
              }"
            >
              {{ subtask.description }}
            </p>
            <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Created: {{ new Date(subtask.created_at).toLocaleDateString() }}
            </span>
          </div>
        </div>
        
        <button 
          @click="deleteSubtask(subtask.id)" 
          class="ml-2 inline-flex rounded p-1 text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/20"
          title="Delete subtask"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
          </svg>
        </button>
      </li>
    </ul>
  </div>
</template>