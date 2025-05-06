<!-- CategoryList.vue -->
<template>
    <div class="mb-4">
        <h5>Категории для парсинга</h5>
        <div v-for="(category, index) in categories" :key="index" class="card mb-2 p-2">
            <div class="row">
                <div class="col">
                    <input v-model="category.name" class="form-control" placeholder="Название категории" />
                </div>
                <div class="col">
                    <input v-model="category.url" class="form-control" placeholder="URL категории" />
                </div>
                <div class="col">
                    <input v-model="category.collectionId" class="form-control" placeholder="ID коллекции" />
                </div>
                <div class="col-auto">
                    <button type="button" @click="$emit('remove-category', index)" class="btn btn-danger">Удалить</button>
                </div>
            </div>
            <!-- Добавляем компонент PriceList -->
            <PriceList
                :prices="category.prices"
                @add-price="addPrice(index)"
                @remove-price="removePrice(index, $event)"
            />
        </div>
        <button type="button" @click="$emit('add-category')" class="btn btn-primary mt-2">Добавить категорию</button>
    </div>
</template>

<script>
import PriceList from './PriceList.vue';

export default {
    components: { PriceList },
    props: {
        categories: {
            type: Array,
            required: true,
        },
    },
    emits: ['add-category', 'remove-category'],
    methods: {
        addPrice(categoryIndex) {
            this.categories[categoryIndex].prices.push({
                condition: null,
                sign: '+',
                margin: null,
            });
        },
        removePrice(categoryIndex, priceIndex) {
            this.categories[categoryIndex].prices.splice(priceIndex, 1);
        },
    },
};
</script>

<style scoped>
.card {
    border: 1px solid #ddd;
}
</style>

