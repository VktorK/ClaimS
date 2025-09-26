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

// Настройка роутера
const routes = [
    { path: '/', component: Home },
    { path: '/about', component: About },
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/profile', component: Profile },
    { path: '/profile/edit', component: EditProfile },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Создание Vue приложения
const app = createApp(App);
app.use(router);
app.mount('#app');
