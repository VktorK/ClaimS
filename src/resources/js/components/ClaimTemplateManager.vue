<template>
  <div class="template-manager">
    <div class="template-manager-header">
      <h2>Управление шаблонами претензий</h2>
      <button @click="showCreateForm" class="btn btn-primary">
        ➕ Создать шаблон
      </button>
    </div>

    <!-- Поиск и фильтры -->
    <div class="filters-section">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          @input="debouncedSearch"
          type="text" 
          placeholder="Поиск по названию или описанию..."
          class="form-control"
        />
      </div>
      <div class="filter-checkbox">
        <label>
          <input 
            v-model="showActiveOnly" 
            @change="loadTemplates"
            type="checkbox"
          />
          Только активные
        </label>
      </div>
    </div>

    <!-- Список шаблонов -->
    <div class="templates-grid" v-if="!loading">
      <div v-for="template in templates" :key="template.id" class="template-card" @click="viewTemplate(template)">
        <div class="template-header">
          <h3 class="template-name" :title="template.name">
            {{ template.name }}
          </h3>
          <div class="template-actions" @click.stop>
            <button @click="viewTemplate(template)" class="btn btn-sm btn-info" title="Просмотр">
              👁️
            </button>
            <button @click="editTemplate(template)" class="btn btn-sm btn-warning" title="Редактировать">
              ✏️
            </button>
            <button @click="deleteTemplate(template)" class="btn btn-sm btn-danger" title="Удалить">
              🗑️
            </button>
          </div>
        </div>
        
        <div class="template-info">
          <div class="info-item" v-if="template.description">
            <span>{{ template.description }}</span>
          </div>
          
          <div class="template-status">
            <span :class="['status-badge', template.is_active ? 'active' : 'inactive']">
              {{ template.is_active ? 'Активен' : 'Неактивен' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Состояние загрузки -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Загрузка шаблонов...</p>
    </div>

    <!-- Состояние пустого списка -->
    <div v-if="!loading && templates.length === 0" class="empty-state">
      <div class="empty-icon">📄</div>
      <h3>Шаблоны не найдены</h3>
      <p v-if="searchQuery">Попробуйте изменить поисковый запрос</p>
      <p v-else>Создайте первый шаблон, нажав кнопку "Создать шаблон"</p>
    </div>

    <!-- Модальные окна -->
    <ClaimTemplateForm 
      v-if="showForm" 
      :template="selectedTemplate"
      @close="closeForm"
      @saved="onTemplateSaved"
    />
    
    <ClaimTemplateViewForm 
      v-if="showViewForm" 
      :template="selectedTemplate"
      @close="closeViewForm"
      @edit="editTemplateFromView"
      @delete="deleteTemplateFromView"
    />
  </div>
</template>

<script>
import { ClaimTemplateAPI } from '../services/api.js'
import ClaimTemplateForm from './ClaimTemplateForm.vue'
import ClaimTemplateViewForm from './ClaimTemplateViewForm.vue'

export default {
  name: 'ClaimTemplateManager',
  components: {
    ClaimTemplateForm,
    ClaimTemplateViewForm
  },
  data() {
    return {
      templates: [],
      loading: false,
      searchQuery: '',
      searchTimeout: null,
      showActiveOnly: true,
      showForm: false,
      showViewForm: false,
      selectedTemplate: null
    }
  },
  mounted() {
    this.loadTemplates()
  },
  methods: {
    async loadTemplates() {
      this.loading = true
      try {
        const response = await ClaimTemplateAPI.getTemplates(
          this.searchQuery, 
          this.showActiveOnly
        )
        if (response.success) {
          this.templates = response.data
        } else {
          console.error('Ошибка загрузки шаблонов:', response.message)
        }
      } catch (error) {
        console.error('Ошибка загрузки шаблонов:', error)
      } finally {
        this.loading = false
      }
    },
    
    debouncedSearch() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.loadTemplates()
      }, 300)
    },
    
    showCreateForm() {
      this.selectedTemplate = null
      this.showForm = true
    },
    
    editTemplate(template) {
      this.selectedTemplate = template
      this.showForm = true
    },
    
    viewTemplate(template) {
      this.selectedTemplate = template
      this.showViewForm = true
    },
    
    async deleteTemplate(template) {
      if (confirm(`Вы уверены, что хотите удалить шаблон "${template.name}"?`)) {
        try {
          const response = await ClaimTemplateAPI.deleteTemplate(template.id)
          if (response.success) {
            this.loadTemplates()
          } else {
            console.error('Ошибка удаления шаблона:', response.message)
          }
        } catch (error) {
          console.error('Ошибка удаления шаблона:', error)
        }
      }
    },
    
    closeForm() {
      this.showForm = false
      this.selectedTemplate = null
    },
    
    closeViewForm() {
      this.showViewForm = false
      this.selectedTemplate = null
    },
    
    onTemplateSaved() {
      this.closeForm()
      this.loadTemplates()
    },
    
    editTemplateFromView(template) {
      this.closeViewForm()
      this.selectedTemplate = template
      this.showForm = true
    },
    
    deleteTemplateFromView(template) {
      this.closeViewForm()
      this.deleteTemplate(template)
    }
  }
}
</script>

<style scoped>
.template-manager {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.template-manager-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.template-manager-header h2 {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  color: #333;
}

.filters-section {
  display: flex;
  gap: 20px;
  margin-bottom: 24px;
  align-items: center;
}

.search-box {
  flex: 1;
  max-width: 400px;
}

.filter-checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-checkbox label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
  color: #666;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.template-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s;
  cursor: pointer;
}

.template-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.template-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.template-name {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #333;
  flex: 1;
  margin-right: 12px;
}

.template-actions {
  display: flex;
  gap: 8px;
  min-width: 100px;
}

.template-actions .btn {
  min-width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.template-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-item {
  font-size: 14px;
  color: #666;
  line-height: 1.4;
}

.template-status {
  display: flex;
  justify-content: flex-end;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #f8d7da;
  color: #721c24;
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
