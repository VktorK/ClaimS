import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './components/App.vue';

// Импорт компонентов
import Home from './components/Home.vue';
import About from './components/About.vue';
import Login from './components/Login.vue';
import Register from './components/Register.vue';
import Profile from './components/Profile.vue';
import EditProfile from './components/EditProfile.vue';

// Импорт страниц для управления
import Products from './pages/Products.vue';
import Sellers from './pages/Sellers.vue';
import Claims from './pages/Claims.vue';

// Настройка роутера
const routes = [
    { path: '/', component: Home },
    { path: '/about', component: About },
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/profile', component: Profile },
    { path: '/profile/edit', component: EditProfile },
    
    // Маршруты для управления продуктами и продавцами
    { 
        path: '/products', 
        component: Products,
        meta: { requiresAuth: true, title: 'Товары' }
    },
    { 
        path: '/sellers', 
        component: Sellers,
        meta: { requiresAuth: true, title: 'Продавцы' }
    },
    { 
        path: '/claims', 
        component: Claims,
        meta: { requiresAuth: true, title: 'Претензии' }
    },
    
    // Редирект с dashboard на главную (можно изменить позже)
    { path: '/dashboard', redirect: '/' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Проверка аутентификации для защищенных маршрутов
router.beforeEach((to, from, next) => {
    const token = typeof localStorage !== 'undefined' ? localStorage.getItem('token') : null;
    
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next('/login');
        } else {
            next();
        }
    } else {
        next();
    }
    
    // Обновление заголовка страницы
    if (to.meta.title) {
        document.title = `${to.meta.title} - Claims System`;
    }
});

// Создание Vue приложения
const app = createApp(App);

// Добавим глобальную систему уведомлений
app.config.globalProperties.$toast = {
    success(message) {
        console.log('✅ Success:', message);
        // Здесь можно добавить более сложную систему уведомлений
        // Toast уведомления уже настроены в компонентах
    },
    error(message) {
        console.error('❌ Error:', message);
        // Toast уведомления уже настроены в компонентах
    },
    info(message) {
        console.log('ℹ️ Info:', message);
        // Toast уведомления уже настроены в компонентах
    }
};

app.use(router);
app.mount('#app');
