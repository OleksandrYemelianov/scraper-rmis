<template>
    <div class="container mt-4">
        <!-- Закладки -->
        <ul class="nav nav-tabs">
            <li class="nav-item" v-for="source in sources" :key="source.id">
                <a
                    class="nav-link"
                    :class="{ active: activeSource == source.id }"
                    @click="loadSource(source.id)"
                >
                    {{ source.name }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" @click="addSource">+ Добавить источник</a>
            </li>
        </ul>

        <!-- Содержимое табов -->
        <div class="mt-4">
            <div v-if="activeSource">
                <div class="mb-4">
                    <div class="row">
                        <div class="col">
                            <div class="progress-container">
                                <div id="progress-text" class="m-3 text-center">Обработано: {{sourceData.parse.processed}} / Всего: {{sourceData.parse.all}}</div>
                                <div id="progress-bar">
                                    <div id="progress" :style="{ width: sourceData.parse.percent + '%' }"></div>
                                </div>
                                <div id="progress-text" class="m-3 text-center">Готово: {{sourceData.parse.percent}}%</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col text-end">
                            <button type="button" class="btn btn-primary me-3" @click="refreshParsing">Обновить</button>
                            <button type="button" class="btn btn-warning me-3" @click="runParsing">Парсить</button>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="saveProfile" class="p-3">
                    <input v-model="sourceData.name" type="hidden" class="form-control" />
                    <!-- Авторизация -->
                    <h5>Авторизация</h5>
                    <div class="mb-4">
                        <div class="row">
                            <div class="col">
                                <label class="form-label">Логин</label>
                                <input v-model="sourceData.login" type="text" class="form-control" />
                            </div>
                            <div class="col">
                                <label class="form-label">Пароль</label>
                                <input v-model="sourceData.password" type="text" class="form-control" />
                            </div>
                        </div>
                    </div>

                    <!-- Авторизация -->
                    <h5>Proxy</h5>
                    <div class="mb-4">
                        <div class="row">
                            <div class="col">
                                <label class="form-label">Хост</label>
                                <input v-model="sourceData.proxy.host" type="text" class="form-control" />
                            </div>
                            <div class="col">
                                <label class="form-label">Порт</label>
                                <input v-model="sourceData.proxy.port" type="number" class="form-control" />
                            </div>
                            <div class="col">
                                <label class="form-label">Логин</label>
                                <input v-model="sourceData.proxy.login" type="text" class="form-control" />
                            </div>
                            <div class="col">
                                <label class="form-label">Пароль</label>
                                <input v-model="sourceData.proxy.password" type="text" class="form-control" />
                            </div>
                        </div>
                    </div>

                    <!-- Общее -->
                    <div class="mb-4">
                        <h5>Общее</h5>
                        <div class="row align-items-end">
                            <div class="col-6">
                                <label class="form-label">Поставщик</label>
                                <input v-model="sourceData.diler" type="text" class="form-control" />
                            </div>
                            <div class="col-1">
                                <label class="form-label">НДС,%</label>
                                <input v-model="sourceData.nds" type="number" class="form-control" />
                            </div>
                            <div class="col-5">
                                <fieldset>
                                    <label class="form-label fw-medium">Формула артикула</label>
                                    <div class="row">
                                        <div class="col-4">
                                            <label class="form-label">Начало</label>
                                            <input v-model="sourceData.sku.begin" type="text" class="form-control" />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">Цифры</label>
                                            <input v-model="sourceData.sku.number" type="number" class="form-control" />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">Конец</label>
                                            <input v-model="sourceData.sku.end" type="text" class="form-control" />
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>

                    <!-- Что парсим ? -->
                    <h5>Что парсим ?</h5>
                    <div class="mb-4">
                        <div class="row">
                            <div class="col">
                                <div class="form-check form-switch">
                                    <input
                                        v-model="sourceData.parseDesc"
                                        class="form-check-input"
                                        type="checkbox"
                                        :id="`whatParseDesc-${activeSource}`"
                                        :name="`sourceData.parseDesc-${activeSource}`"
                                    />
                                    <label class="form-check-label" :for="`whatParseDesc-${activeSource}`">Описание товара</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-check form-switch">
                                    <input
                                        v-model="sourceData.parsePhoto"
                                        class="form-check-input"
                                        type="checkbox"
                                        :id="`whatParsePhoto-${activeSource}`"
                                        :name="`sourceData.parsePhoto-${activeSource}`"
                                    />
                                    <label class="form-check-label" :for="`whatParsePhoto-${activeSource}`">Фото товара</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Список категорий -->
                    <CategoryList :categories="sourceData.categories" @add-category="addCategory" @remove-category="removeCategory" />

                    <div class="mb-4">
                        <div class="row">
                            <div class="col text-end">
                                <button type="button" class="btn btn-danger me-3" @click="removeProfile">Удалить</button>
                                <button type="submit" class="btn btn-success">Сохранить</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div v-else class="alert alert-info">
                <p>Выберите источник или добавьте новый, чтобы начать работу.</p>
                <button class="btn btn-primary mt-2" @click="addSource">Добавить первый источник</button>
            </div>
        </div>
    </div>
</template>

<script>
import CategoryList from '@/components/CategoryList.vue';
import api from '@/services/api';

export default {
    components: { CategoryList },
    data() {
        return {
            sources: [],
            activeSource: null,
            cachedData: {},
            sourceData: {
                name: '',
                login: '',
                password: '',
                diler: '',
                nds: null,
                proxy: {
                    host: '',
                    port: null,
                    login: '',
                    password: ''
                },
                sku: {
                    begin: '',
                    number: null,
                    end: '',
                    password: ''
                },
                parseDesc: true,
                parsePhoto: true,
                categories: [],

                parse: {
                    processed: 0,
                    all: 0,
                    percent: 40
                },
            },
        };
    },
    async created() {
        this.sources = await api.getProfiles();
        const lastActiveSource = this.getActiveProfile();

        if (lastActiveSource) {
            await this.loadSource(+lastActiveSource);
        } else if (this.sources.length > 0) {
            await this.loadSource(this.sources[0].id);
        }
    },
    methods: {
        async loadSource(sourceId) {
            if (!this.cachedData[sourceId]) {
                const data = await api.getPofile(sourceId);
                let parseStatePercent = 0;
                const  parseState = await api.getState(sourceId);
                if (parseState.all && parseState.all) {
                    parseStatePercent = Math.round( +parseState.current / +parseState.all * 100 );
                }

                this.cachedData[sourceId] = {
                    name: data.name || '',
                    login: data.login || '',
                    password: data.password || '',
                    diler: data.diler || '',
                    nds: data.nds || null,
                    proxy: {
                        host: data.proxy.host || '',
                        port: data.proxy.port || null,
                        login: data.proxy.login || '',
                        password: data.proxy.password || ''
                    },
                    sku: {
                        begin: data.sku.begin || '',
                        number: data.sku.number || null,
                        end: data.sku.end || '',
                        password: data.sku.password || ''
                    },
                    parseDesc: data.parseDesc == '1' ? true : false,
                    parsePhoto: data.parsePhoto == '1' ? true : false,
                    categories: data.categories.map(category => ({
                        ...category,
                        prices: category.prices || [], // Убедимся, что prices есть
                    })) || [],
                    parse: {
                        processed: parseState.current || 0,
                        all: parseState.all || 0,
                        percent: parseStatePercent
                    },
                };
            }
            this.sourceData = { ...this.cachedData[sourceId] };
            this.setActiveProfile(sourceId);
        },

        addSource() {
            const name = prompt('Введите название источника:');
            if (name) {
                api.createProfile({ name }).then((newSource) => {
                    this.sources.push(newSource);
                    this.loadSource(newSource.id);
                });
            }
        },
        addCategory() {
            this.sourceData.categories.push({
                name: '',
                url: '',
                collectionId: '',
                prices: [],
            });
        },
        removeCategory(index) {
            this.sourceData.categories.splice(index, 1);
        },
        async saveProfile() {
            await api.updateProfile(this.activeSource, this.sourceData);
            this.cachedData[this.activeSource] = { ...this.sourceData };
            alert('Профиль сохранён!');
        },
        async removeProfile() {
            const index = this.sources.findIndex(item => item.id === this.activeSource);
            const permit = confirm('Вы действительно хотите удалить ' + this.sources[ index ].name + ' ?');
            if (permit) {
                await api.removeProfile(this.activeSource);
                this.sources.splice(index, 1);
                if (this.cachedData.hasOwnProperty(this.activeSource)) {
                    delete this.cachedData[this.activeSource];
                }
                this.setActiveProfile(this.sources.length > 0 ? this.sources[0].id : 0);
            }
        },
        setActiveProfile(id) {
            this.activeSource = id;
            localStorage.setItem('lastActiveSource', id);
        },
        getActiveProfile() {
            return localStorage.getItem('lastActiveSource');
        },
        async runParsing() {
            const parseState = await api.runParsing(this.activeSource);
            this.setParsingProcess(parseState);
            if (+parseState.complete !== 1) {
                await this.runParsing();
            }
        },
        setParsingProcess(parseState) {
            let parseStatePercent = 0;
            if (parseState.all && parseState.all) {
                parseStatePercent = Math.round( +parseState.current / +parseState.all * 100 );
            }
            this.sourceData.parse = {
                processed: parseState.current,
                all: parseState.all,
                percent: parseStatePercent
            }
        },
        async refreshParsing() {
            const parseState = await api.refreshParsing(this.activeSource);
            this.setParsingProcess(parseState);
        },
    },
};
</script>

<style scoped>
.nav-link {
    cursor: pointer;
}
.nav-link.active {
    background-color: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
}
.alert-info {
    background-color: #e9f0fa;
    border-color: #cce5ff;
    color: #004085;
    padding: 15px;
    border-radius: 5px;
}
#progress-bar {
    width: 100%;
    height: 20px;
    background-color: #ddd;
    border-radius: 10px;
    overflow: hidden;
}
#progress {
    width: 30%;
    height: 100%;
    background-color: #4CAF50;
}
</style>