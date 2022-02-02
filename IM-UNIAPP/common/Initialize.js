//引入vuex
import store from 'store/index.js';
import Vue from 'vue';
//初始化
function lnit() {
	if(store.state.userInfo == ''){
		//用户基本信息接口
		Vue.prototype.$u.http.post('User/getUserInfoByID', {}).then(res => {
			if(res.status == 200){
				store.commit('setUser',res.data);
			}
		})
	}
	

	}
	export {
		lnit
	}
