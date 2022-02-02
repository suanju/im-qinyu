import {
	RouterMount,
	createRouter
} from 'uni-simple-router';
//初始化js
import {
	lnit
} from './common/Initialize.js';
const router = createRouter({
	platform: process.env.VUE_APP_PLATFORM,
	routes: [...ROUTES],
	
});
//全局路由前置守卫
router.beforeEach((to, from, next) => {
	//判断是否登入
	let token = uni.getStorageSync('token');
	if (to.name == 'login' || to.name == 'forget' || to.name == 'register') {
		next()
	} else {
		if (token) {
			//初始化vuex数据
			lnit();
			next();
		} else {
			next({
				name: 'login'
			});
		}
	}
});
// 全局路由后置守卫
router.afterEach((to, from) => {})

export {
	router,
	RouterMount
}
