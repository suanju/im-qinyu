import App from './App';

// #ifndef VUE3
import Vue from 'vue';
// 引入uView
import uView from "uview-ui";
//引入静态资源地址
import {
	staticUrl,
	wsUrl
} from './common/config.js';
//引入vuex
import store from 'store/index.js';
// uViewhttp拦截器
import httpInterceptor from '@/common/http.interceptor.js';
//路由守卫
import {router,RouterMount} from './router.js';
//地图需要jsonp
import { VueJsonp } from 'vue-jsonp/dist';


Vue.use(uView);
Vue.use(router);
Vue.use(httpInterceptor, app); 
Vue.use(VueJsonp);


Vue.config.productionTip = false;
//挂载全局
Vue.prototype.staticUrl = staticUrl;
Vue.prototype.wsUrl = wsUrl;
App.mpType = 'app'
const app = new Vue({
	store,
	...App
})

// #endif

// #ifdef VUE3
import {
	createSSRApp
} from 'vue'
export function createApp() {
	const app = createSSRApp(App)
	return {
		app
	}
}
// #endif
//路由守卫配置
// #ifdef H5
	RouterMount(app,router,'#app')
// #endif

// #ifndef H5
	app.$mount(); //为了兼容小程序及app端必须这样写才有效果
// #endif