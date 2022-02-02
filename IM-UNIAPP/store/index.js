import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex); //vue的插件机制

//Vuex.Store 构造器选项
const store = new Vuex.Store({
	state: {
		//用户信息
		"userInfo": [],
		"newFriend":[],
		"newGroup":[],
		//tabbar
		"tabbar": [{ 
				iconPath: "/static/icon/xiaoxi.png",
				selectedIconPath: "/static/icon/xiaoxi_select.png",
				text: '消息',
				count: 0,
				isDot: false,
				pagePath: "/pages/chat/chatList"
			},
			{
				iconPath: "/static/icon/lianxiren.png",
				selectedIconPath: "/static/icon/lianxiren_select.png",
				text: '联系人',
				count: 0,
				isDot: false,
				pagePath: "/pages/contact/index"
			},
			{
				iconPath: "/static/icon/kongjian.png",
				selectedIconPath: "/static/icon/kongjian_select.png",
				text: '动态',
				count: 0,
				isDot: false,
				pagePath: "/pages/dynamic/index"
			},
		]
	},
	mutations: {
		//将用户信息存到vuex
		setUser(state,info) {
			state.userInfo = info;
		},
		setTabbar(state,info) {
			state.tabbar = info;
		},
		setNewFriend(state,info) {
			state.newFriend = [...state.newFriend,info];
		},
		setNewGroup(state,info){
			state.newGroup = [...state.newGroup,info];
		},
		resetNewGroup(state,info){
			state.newGroup = info;
		}
	}
})
export default store
