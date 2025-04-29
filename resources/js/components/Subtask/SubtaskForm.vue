<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
  taskId: number;
}>();

const form = useForm({
  task_id: props.taskId,
  title: '',
  description: '',
});

const isSubmitting = ref(false);

const submitForm = () => {
  isSubmitting.value = true;
  form.post(route('subtasks.store'), {
    preserveScroll: true,
    onSuccess: () => {
        form.reset('title', 'description');
        isSubmitting.value = false;
        console.log('Subtask added successfully');
    },
    onFinish: () => {
      form.reset('title', 'description');
      isSubmitting.value = false;
    },
  });
};
</script>

<template>
  <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <h3 class="mb-3 text-lg font-medium">Add Subtask</h3>
    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label for="title" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          placeholder="Enter subtask title"
          required
        />
        <div v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</div>
      </div>
      
      <div>
        <label for="description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea
          id="description"
          v-model="form.description"
          class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          placeholder="Enter subtask description"
          rows="3"
          required
        ></textarea>
        <div v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</div>
      </div>
      
      <div>
        <button
          type="submit"
          class="inline-flex items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-70"
          :disabled="form.processing || isSubmitting"
        >
          <svg
            v-if="isSubmitting || form.processing"
            class="mr-2 h-4 w-4 animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
          <span v-if="form.processing || isSubmitting">Adding...</span>
          <span v-else>Add Subtask</span>
        </button>
      </div>
    </form>
  </div>
</template> 