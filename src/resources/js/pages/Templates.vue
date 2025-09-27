<template>
  <div class="templates-page">
    <div class="page-header">
      <h1>Шаблоны претензий</h1>
      <p class="page-description">
        Создавайте и управляйте шаблонами претензий для быстрого заполнения документов
      </p>
    </div>
    
    <ClaimTemplateManager />
  </div>
</template>

<script>
import ClaimTemplateManager from '../components/ClaimTemplateManager.vue'

export default {
  name: 'Templates',
  components: {
    ClaimTemplateManager
  }
}
</script>

<style scoped>
.templates-page {
  min-height: 100vh;
  background: #f8f9fa;
}

.page-header {
  background: white;
  padding: 30px;
  border-bottom: 1px solid #e9ecef;
  margin-bottom: 30px;
}

.page-header h1 {
  margin: 0 0 10px 0;
  font-size: 32px;
  font-weight: 700;
  color: #333;
}

.page-description {
  margin: 0;
  font-size: 16px;
  color: #666;
  line-height: 1.5;
}
</style>
