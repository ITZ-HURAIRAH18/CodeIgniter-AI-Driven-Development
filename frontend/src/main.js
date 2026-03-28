import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import permissionDirective from '@/directives/permission'
import './assets/global.css'
import './assets/css/main.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)

// Register global permission directive
app.directive('permission', permissionDirective)

app.mount('#app')
