<template>
  <div class="consumers-page">
    <div class="page-header">
      <h1>Потребители</h1>
      <button @click="showCreateForm" class="btn btn-primary">
        ➕ Добавить потребителя
      </button>
    </div>

    <!-- Фильтры и поиск -->
    <div class="filters-section">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          @input="debouncedSearch"
          type="text" 
          placeholder="Поиск по имени, паспорту или ИНН..."
          class="form-control"
        />
      </div>
    </div>

    <!-- Список потребителей -->
    <div class="consumers-grid" v-if="!loading">
      <div v-for="consumer in consumers" :key="consumer.id" class="consumer-card">
        <div class="consumer-header">
          <h3 class="consumer-name" :title="consumer.full_name">
            {{ consumer.full_name }}
          </h3>
          <div class="consumer-actions">
            <button @click="viewConsumer(consumer)" class="btn btn-sm btn-info" title="Просмотр">
              👁️
            </button>
            <button @click="editConsumer(consumer)" class="btn btn-sm btn-warning" title="Редактировать">
              ✏️
            </button>
            <button @click="deleteConsumer(consumer)" class="btn btn-sm btn-danger" title="Удалить">
              🗑️
            </button>
          </div>
        </div>
        
        <div class="consumer-info">
          <div class="info-item" v-if="consumer.passport">
            🆔 <span>Паспорт: {{ consumer.formatted_passport }}</span>
          </div>
          
          <div class="info-item" v-if="consumer.inn">
            🏢 <span>ИНН: {{ consumer.formatted_inn }}</span>
          </div>
          
          <div class="info-item" v-if="consumer.address">
            📍 <span>{{ consumer.address }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Состояние загрузки -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Загрузка потребителей...</p>
    </div>

    <!-- Состояние пустого списка -->
    <div v-if="!loading && consumers.length === 0" class="empty-state">
      <div class="empty-icon">👥</div>
      <h3>Потребители не найдены</h3>
      <p v-if="searchQuery">Попробуйте изменить поисковый запрос</p>
      <p v-else>Добавьте первого потребителя, нажав кнопку "Добавить потребителя"</p>
    </div>

    <!-- Модальные окна -->
    <ConsumerForm 
      v-if="showForm" 
      :consumer="selectedConsumer"
      @close="closeForm"
      @saved="onConsumerSaved"
    />
    
    <ConsumerViewForm 
      v-if="showViewForm" 
      :consumer="selectedConsumer"
      @close="closeViewForm"
      @edit="editConsumerFromView"
      @delete="deleteConsumerFromView"
    />
  </div>
</template>

<script>
import { ConsumerAPI } from '../services/api.js'
import ConsumerForm from '../components/ConsumerForm.vue'
import ConsumerViewForm from '../components/ConsumerViewForm.vue'

export default {
  name: 'Consumers',
  components: {
    ConsumerForm,
    ConsumerViewForm
  },
  data() {
    return {
      consumers: [],
      loading: false,
      searchQuery: '',
      searchTimeout: null,
      showForm: false,
      showViewForm: false,
      selectedConsumer: null
    }
  },
  mounted() {
    this.loadConsumers()
  },
  methods: {
    async loadConsumers() {
      this.loading = true
      try {
        const response = await ConsumerAPI.getConsumers(this.searchQuery)
        if (response.success) {
          this.consumers = response.data
        } else {
          console.error('Ошибка загрузки потребителей:', response.message)
        }
      } catch (error) {
        console.error('Ошибка загрузки потребителей:', error)
      } finally {
        this.loading = false
      }
    },
    
    debouncedSearch() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.loadConsumers()
      }, 300)
    },
    
    showCreateForm() {
      this.selectedConsumer = null
      this.showForm = true
    },
    
    editConsumer(consumer) {
      this.selectedConsumer = consumer
      this.showForm = true
    },
    
    viewConsumer(consumer) {
      this.selectedConsumer = consumer
      this.showViewForm = true
    },
    
    async deleteConsumer(consumer) {
      if (confirm(`Вы уверены, что хотите удалить потребителя "${consumer.full_name}"?`)) {
        try {
          const response = await ConsumerAPI.deleteConsumer(consumer.id)
          if (response.success) {
            this.loadConsumers()
          } else {
            console.error('Ошибка удаления потребителя:', response.message)
          }
        } catch (error) {
          console.error('Ошибка удаления потребителя:', error)
        }
      }
    },
    
    closeForm() {
      this.showForm = false
      this.selectedConsumer = null
    },
    
    closeViewForm() {
      this.showViewForm = false
      this.selectedConsumer = null
    },
    
    onConsumerSaved() {
      this.closeForm()
      this.loadConsumers()
    },
    
    editConsumerFromView(consumer) {
      this.closeViewForm()
      this.selectedConsumer = consumer
      this.showForm = true
    },
    
    deleteConsumerFromView(consumer) {
      this.closeViewForm()
      this.deleteConsumer(consumer)
    }
  }
}
</script>

<style scoped>
.consumers-page {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  color: #333;
}

.filters-section {
  margin-bottom: 24px;
}

.search-box {
  max-width: 400px;
}

.consumers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.consumer-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.2s;
}

.consumer-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.consumer-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.consumer-name {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #333;
  flex: 1;
  margin-right: 12px;
}

.consumer-actions {
  display: flex;
  gap: 8px;
  min-width: 100px;
}

.consumer-actions .btn {
  min-width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.consumer-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #666;
}

.info-item span {
  flex: 1;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  color: #333;
}

.empty-state p {
  margin: 0;
  color: #666;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-info {
  background-color: #17a2b8;
  color: white;
}

.btn-info:hover {
  background-color: #138496;
}

.btn-warning {
  background-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #e0a800;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 12px;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}
</style>
