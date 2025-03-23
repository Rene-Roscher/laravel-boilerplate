import { App } from "vue";
import axios from 'axios'

window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

export default {
  install (app: App) {
    // Todo interceptors here
  }
}
