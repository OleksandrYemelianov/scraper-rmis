<!-- PriceList.vue -->
<template>
    <div class="mb-3 mt-3">
        <h6>Цены</h6>
        <div v-for="(price, index) in prices" :key="index" class="row align-items-center mb-2">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text">Условие</span>
                    <input
                        v-model.number="price.condition"
                        type="number"
                        class="form-control"
                        placeholder="Условие"
                    />
                </div>
            </div>
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text">Знак</span>
                    <select v-model="price.sign" class="form-select">
                        <option value="+">+</option>
                        <option value="%">%</option>
                        <option value="%">-</option>
                    </select>
                </div>
            </div>
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text">Маржа</span>
                    <input
                        v-model.number="price.margin"
                        type="number"
                        class="form-control"
                        placeholder="Маржа"
                    />
                </div>
            </div>
            <div class="col-auto">
                <button
                    type="button"
                    @click="$emit('remove-price', index)"
                    class="btn btn-danger btn-sm"
                >
                    X
                </button>
            </div>
        </div>
        <button
            type="button"
            @click="$emit('add-price')"
            class="btn btn-primary btn-sm mt-2"
        >
            Добавить цену
        </button>
    </div>
</template>

<script>
export default {
    props: {
        prices: {
            type: Array,
            required: true,
        },
    },
    emits: ['add-price', 'remove-price'],
};
</script>

<style scoped>
.form-control, .form-select {
    font-size: 0.9rem;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.9rem;
}
</style>