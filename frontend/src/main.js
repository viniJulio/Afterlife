import { createApp } from 'vue'
import router from './router'
import VueTheMask from 'vue-the-mask'

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// Icons
import '@mdi/font/css/materialdesignicons.css'
import { aliases as faAliases, fa } from 'vuetify/iconsets/fa-svg'
import { aliases as mdiAliases, mdi } from 'vuetify/iconsets/mdi'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons' // Importa o conjunto de ícones de marcas

// Components
import App from './App.vue'

// Adiciona os ícones do FontAwesome
library.add(fas, far, fab) // Inclui o conjunto de marcas

const vuetify = createVuetify({
  components,
  directives,
  icons: {
    defaultSet: 'mdi', // Define mdi como o conjunto padrão (ou 'fa' se preferir FontAwesome)
    aliases: {
      ...faAliases,
      ...mdiAliases,
    },
    sets: {
      fa,
      mdi,
    },
  },
})

createApp(App)
  .use(router)
  .use(vuetify)
  .use(VueTheMask)
  .component('font-awesome-icon', FontAwesomeIcon)
  .mount('#app')
