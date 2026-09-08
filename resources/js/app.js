import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ✅ ONLY IMPORT BOOTSTRAP (DO NOT CONFIGURE ECHO HERE)
import './bootstrap';
import '../css/app.css';

console.log('APP JS LOADED');