<template>
  <div class="home" style="padding: 40px 24px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
      <h1 style="font-size: 48px; font-weight: 700; line-height: 120%; color: #303030; margin-bottom: 24px;">
        <span v-if="isAuthenticated">Добро пожаловать, {{ user?.profile?.full_name || user?.name }}!</span>
        <span v-else>Добро пожаловать в Claims App</span>
      </h1>
      <p style="font-size: 20px; font-weight: 400; line-height: 140%; color: #767676; margin-bottom: 48px;">
        <span v-if="isAuthenticated">Рады видеть вас в нашем приложении!</span>
        <span v-else>Современное приложение для управления заявками с Vue 3 и Laravel</span>
      </p>

      <!-- API Status -->
      <div style="margin-bottom: 48px;">
        <div class="card-main" style="max-width: 400px; margin: 0 auto;">
          <h3 style="font-size: 24px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">Статус API</h3>
          <div v-if="apiStatus.loading" style="color: #0077ff;">
            Загрузка...
          </div>
          <div v-else-if="apiStatus.success" style="color: #14AE5C;">
            ✅ API подключен успешно
          </div>
          <div v-else style="color: #EC221F;">
            ❌ Ошибка подключения к API
          </div>
          <div v-if="apiData" style="margin-top: 8px; font-size: 14px; color: #767676;">
            {{ apiData.message }}
          </div>
        </div>
      </div>

      <!-- Отладочная информация -->
      <div style="margin-bottom: 20px; padding: 20px; background: #f0f0f0; border-radius: 8px; text-align: left;">
        <h4>Отладочная информация:</h4>
        <p><strong>Авторизован:</strong> {{ isAuthenticated }}</p>
        <p><strong>Пользователь:</strong> {{ user ? JSON.stringify(user) : 'null' }}</p>
        <p><strong>Токен:</strong> {{ localStorage.getItem('token') ? 'есть' : 'нет' }}</p>
      </div>

      <!-- Быстрые действия для авторизованных пользователей -->
      <div v-if="isAuthenticated" style="margin-bottom: 48px;">
        <h2 style="font-size: 32px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 32px;">
          Управление системой
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
          <div class="action-card" @click="$router.push('/products')">
            <div class="action-icon">
              <i class="fas fa-boxes"></i>
            </div>
            <div class="action-content">
              <h3>Продукты</h3>
              <p>Управление всеми продуктами в системе</p>
              <div class="action-stats">
                <span>Управление продуктами</span>
                <span>Создание, редактирование, удаление</span>
              </div>
            </div>
            <div class="action-arrow">
              <i class="fas fa-arrow-right"></i>
            </div>
          </div>

          <div class="action-card" @click="$router.push('/sellers')">
            <div class="action-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="action-content">
              <h3>Продавцы</h3>
              <p>Управление продавцами и их данными</p>
              <div class="action-stats">
                <span>Управление продавцами</span>
                <span>Данные продавцов и ОГРН</span>
              </div>
            </div>
            <div class="action-arrow">
              <i class="fas fa-arrow-right"></i>
            </div>
          </div>

          <div class="action-card" @click="$router.push('/profile')">
            <div class="action-icon">
              <i class="fas fa-user"></i>
            </div>
            <div class="action-content">
              <h3>Профиль</h3>
              <p>Настройки вашего профиля</p>
              <div class="action-stats">
                <span>{{ user?.profile?.full_name || user?.name || 'Пользователь' }}</span>
              </div>
            </div>
            <div class="action-arrow">
              <i class="fas fa-arrow-right"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Информационные карточки -->
      <div style="margin-bottom: 32px;">
        <h2 style="font-size: 32px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 32px;">
          О системе
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
          <div class="card-main">
            <h3 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">API Backend</h3>
            <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">Laravel 12 с JWT аутентификацией</p>
          </div>

          <div class="card-main">
            <h3 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">Vue 3 Frontend</h3>
            <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">Современный фронтенд с Vue Router</p>
          </div>

          <div class="card-main">
            <h3 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">Docker</h3>
            <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">Контейнеризация для легкого развертывания</p>
          </div>
        </div>
      </div>

      <!-- Превью возможностей для неавторизованных пользователей -->
      <div v-if="!isAuthenticated" style="margin-bottom: 48px;">
        <h2 style="font-size: 32px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 32px;">
          Возможности системы
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
          <div class="action-card preview-card" style="opacity: 0.7;">
            <div class="action-icon">
              <i class="fas fa-boxes"></i>
            </div>
            <div class="action-content">
              <h3>Продукты</h3>
              <p>Управление всеми продуктами в системе</p>
              <div class="action-stats">
                <span>Создание, редактирование, удаление</span>
                <span>Поиск и фильтрация</span>
              </div>
            </div>
            <div class="action-arrow">
              <i class="fas fa-lock"></i>
            </div>
          </div>

          <div class="action-card preview-card" style="opacity: 0.7;">
            <div class="action-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="action-content">
              <h3>Продавцы</h3>
              <p>Управление продавцами и их данными</p>
              <div class="action-stats">
                <span>Данные продавцов и ОГРН</span>
                <span>Статистика и аналитика</span>
              </div>
            </div>
            <div class="action-arrow">
              <i class="fas fa-lock"></i>
            </div>
          </div>
        </div>
        
        <div style="text-align: center; margin-top: 32px;">
          <p style="font-size: 18px; color: #767676; margin-bottom: 24px;">
            Войдите в систему или зарегистрируйтесь, чтобы получить доступ к управлению
          </p>
          <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <router-link to="/login" class="btn btn-primary btn-large">
              <i class="fas fa-sign-in-alt"></i>
              Войти в систему
            </router-link>
            <router-link to="/register" class="btn btn-outline btn-large">
              <i class="fas fa-user-plus"></i>
              Зарегистрироваться
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Home',
  data() {
    return {
      apiStatus: {
        loading: true,
        success: false
      },
      apiData: null,
      isAuthenticated: false,
      user: null
    }
  },
  async mounted() {
    await this.checkApiStatus()
    this.checkAuthStatus()
    // Слушаем изменения авторизации
    window.addEventListener('auth-updated', this.handleAuthUpdate)
  },
  beforeUnmount() {
    window.removeEventListener('auth-updated', this.handleAuthUpdate)
  },
  methods: {
    async checkApiStatus() {
      try {
        const response = await axios.get('/api/info')
        this.apiStatus.success = true
        this.apiData = response.data
      } catch (error) {
        console.error('API Error:', error)
        this.apiStatus.success = false
      } finally {
        this.apiStatus.loading = false
      }
    },
    checkAuthStatus() {
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')
      
      if (token && user) {
        this.isAuthenticated = true
        this.user = JSON.parse(user)
      }
    },
    handleAuthUpdate(event) {
      const { user, authenticated } = event.detail
      this.isAuthenticated = authenticated
      this.user = user
    }
  }
}
</script>

<style scoped>
.action-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 20px;
  border: 2px solid transparent;
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  border-color: #007bff;
}

.action-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  flex-shrink: 0;
}

.action-content {
  flex: 1;
}

.action-content h3 {
  margin: 0 0 8px 0;
  font-size: 20px;
  font-weight: 600;
  color: #333;
}

.action-content p {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #666;
  line-height: 1.4;
}

.action-stats {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.action-stats span {
  font-size: 12px;
  color: #007bff;
  font-weight: 500;
  background: #e3f2fd;
  padding: 4px 8px;
  border-radius: 6px;
  display: inline-block;
  width: fit-content;
}

.action-arrow {
  color: #007bff;
  font-size: 18px;
  opacity: 0.6;
  transition: opacity 0.3s ease;
}

.action-card:hover .action-arrow {
  opacity: 1;
}

.btn {
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.btn-large {
  padding: 16px 32px;
  font-size: 16px;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
  transform: translateY(-2px);
}

.btn-outline {
  background: transparent;
  color: #007bff;
  border-color: #007bff;
}

.btn-outline:hover {
  background: #007bff;
  color: white;
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .action-card {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  
  .action-icon {
    width: 50px;
    height: 50px;
    font-size: 20px;
  }
  
  .action-stats {
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .btn-large {
    padding: 14px 24px;
    font-size: 14px;
  }
}
</style>
