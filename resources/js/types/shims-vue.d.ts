// Global type declarations for Inertia and route functions

declare module '@inertiajs/vue3' {
  export interface PageProps {
    auth: {
      user: any;
    };
    ziggy: any;
    [key: string]: any;
  }

  // Page component with proper props typing
  export const Page: any;
  export const Link: any;
  export const Head: any;
  export const createInertiaApp: any;
  export const usePage: () => { props: PageProps };
  export const router: any;
  
  // Declare useForm with proper generic type support
  export function useForm<TForm extends Record<string, any>>(data: TForm): {
    data: () => TForm;
    errors: Record<keyof TForm, string>;
    processing: boolean;
    progress: {
      percentage: number | null;
    } | null;
    post: (url: string, options?: Record<string, unknown>) => Promise<void>;
    put: (url: string, options?: Record<string, unknown>) => Promise<void>;
    patch: (url: string, options?: Record<string, unknown>) => Promise<void>;
    delete: (url: string, options?: Record<string, unknown>) => Promise<void>;
    reset: (...fields: string[]) => void;
    [key: string]: any;
  };
}

declare module 'vue' {
  interface ComponentCustomProperties {
    route: (name: string, params?: Record<string, any> | undefined, absolute?: boolean) => string;
    $page: {
      props: {
        auth: {
          user: any;
        };
        ziggy: any;
        [key: string]: any;
      }
    };
    [key: string]: any;
  }
  
  // Add exports for ref and watch
  export const ref: any;
  export const watch: any;
}

declare module 'lucide-vue-next' {
  import { DefineComponent } from 'vue';
  
  const Search: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const Plus: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const ArrowDown: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const ArrowUp: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const List: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const BookOpen: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const Folder: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const LayoutGrid: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const CheckCircle: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const Clock: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  const ClipboardList: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
  
  export { 
    Search, Plus, ArrowDown, ArrowUp, List, 
    BookOpen, Folder, LayoutGrid, CheckCircle, 
    Clock, ClipboardList 
  };
}

declare module 'lodash/debounce' {
  export default function debounce<T extends (...args: any[]) => any>(
    func: T,
    wait?: number,
    options?: {
      leading?: boolean;
      maxWait?: number;
      trailing?: boolean;
    }
  ): T;
}

// Global route function
declare function route(name: string, params?: Record<string, any> | number, absolute?: boolean): string;

// Add global properties to Vue template
declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    route: typeof route;
    // Add other globals here
  }
} 