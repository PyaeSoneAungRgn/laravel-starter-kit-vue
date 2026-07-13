<script setup lang="ts">
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { ref, watch } from 'vue';

const props = defineProps<{
    currentPage: number;
    perPage: number;
    total: number;
}>();

const page = ref(props.currentPage);

const emit = defineEmits<{
    (e: 'page-change', page: number): void;
}>();

watch(page, () => {
    emit('page-change', page.value);
});
</script>

<template>
    <Pagination class="justify-end" v-model:page="page" v-slot="{ page }" :items-per-page="perPage" :total="total" :default-page="currentPage">
        <PaginationContent v-slot="{ items }">
            <PaginationPrevious />

            <template v-for="(item, index) in items" :key="index">
                <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === page">
                    {{ item.value }}
                </PaginationItem>
            </template>

            <PaginationEllipsis :index="4" />

            <PaginationNext />
        </PaginationContent>
    </Pagination>
</template>
