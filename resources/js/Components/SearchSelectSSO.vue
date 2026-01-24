<template>
  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">*SSO Kota Samarinda</label>
  <div class="relative w-full">
    <fwb-input v-model="search" @focus="open = true" :required="required" :validation-status="validationStatus" class="flex relative items-center bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white w-full" placeholder="Please select one" />
    <!-- Search Button -->
    <button @click="fetchSSOData" type="button" class="absolute right-2 top-2 text-sm bg-teal-500 text-white px-3 py-1 rounded-full hover:bg-teal-600">
      Search
    </button>
    <ul v-if="open && filteredOptions.length" class="absolute w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white mt-1 z-10 max-h-48 overflow-auto">
      <li v-for="option in filteredOptions" :key="option.value" @click="selectOption(option)" class="px-3 py-2 cursor-pointer hover:bg-gray-200 bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-800">
        {{ option.label }}
      </li>
    </ul>
  </div>
  <small class="text-gray-500 text-sm">Pencarian menggunakan <strong>Nama, NIP, Email</strong> dan pastikan akun yang akan ditambahkan sudah terdaftar di <a href="https://sso.samarindakota.go.id" class="text-gray-900 dark:text-gray-100 font-semibold" target="_blank">sso.samarindakota.go.id</a>.</small>
</template>
<script>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import axios from "axios";

export default {
  props: {
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
      default: false,
    },
  },
  setup(props, { emit }) {
    const search = ref("");
    const open = ref(false);
    const filteredOptions = ref([]);
    const toast = useToast();

    const fetchSSOData = async () => {
      if(!search.value || search.value.length < 3) return; // Ensure search string is long enough
      try {
        const token = usePage().props.auth.user.token_sso;
        if(!token) {
          toast.error("Token SSO tidak tersedia.");
          return;
        }

        const response = await axios.get(
          `https://sso.samarindakota.go.id/api/search-user?search=${search.value}`, {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        filteredOptions.value = response.data.map(user => ({
          label: `${user.text}`,
          value: user.id,
        }));
      } catch (error) {
        toast.error("Gagal mengambil data SSO");
      }
    };

    const selectOption = (option) => {
      search.value = option.label;
      open.value = false;
      emit("update:modelValue", option.value);
    };

    return {
      search,
      open,
      filteredOptions,
      fetchSSOData,
      selectOption,
    };
  },
};

</script>
