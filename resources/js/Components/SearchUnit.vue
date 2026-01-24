<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { debounce } from 'lodash';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null,
  },
  required: {
    type: Boolean,
    default: false,
  },
  validationStatus: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const open = ref(false);
const filteredOptions = ref([]);
const toast = useToast();

const fetchData = async () => {
  if(!search.value || search.value.length < 3) {
    filteredOptions.value = [];
    return;
  }

  try {
    const token = usePage().props.auth.user.device_token;
    if(!token) {
      toast.error('Token tidak tersedia.');
      return;
    }

    const response = await axios.get('/api/unit', {
      params: {
        token,
        key: search.value,
      },
    });

    filteredOptions.value = response.data.map(user => ({
      label: `${user.name.toLowerCase()}`,
      value: user.value,
    }));
  } catch (error) {
    toast.error('Gagal mengambil data unit.');
    console.error(error);
  }
};

const debouncedFetchData = debounce(fetchData, 300);

const selectOption = (option) => {
  search.value = option.label;
  open.value = false;
  emit('update:modelValue', option.value);
};

</script>
<template>
  <div class="relative w-full">
    <fwb-input v-model="search" @focus="open = true" @keyup="debouncedFetchData" :required="required" :validation-status="validationStatus" class="flex relative items-center bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white w-full capitalize" placeholder="Cari Unit (min 3 karakter)" />
    <ul v-if="open && filteredOptions.length" class="absolute w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white mt-1 z-10 max-h-48 overflow-auto">
      <li v-for="option in filteredOptions" :key="option.value" @click="selectOption(option)" class="px-3 py-2 cursor-pointer capitalize hover:bg-gray-200 bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-800">
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>
