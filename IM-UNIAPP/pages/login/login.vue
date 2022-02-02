<template>
	<view class="login">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view class="content">
			<!-- 头部logo -->
			<view class="header">
				<image src="../../static/img/login/login.gif"></image>
			</view>
			<!-- 主体表单 -->
			<view class="main">
				<wInput v-model="userName" type="text" maxlength="11" placeholder="用户名" :focus="isFocus"></wInput>
				<wInput v-model="passData" type="password" maxlength="11" placeholder="密码"></wInput>
			</view>
			<wButton class="wbutton" text="登 录" :rotate="isRotate" @click="startLogin"></wButton>

			<!-- 其他登录 -->
			<!-- 	<view class="other_login cuIcon">
				<view class="login_icon">
					<view class="cuIcon-weixin" @tap="login_weixin"></view>
				</view>
				<view class="login_icon">
					<view class="cuIcon-weibo" @tap="login_weibo"></view>
				</view>
				<view class="login_icon">
					<view class="cuIcon-github" @tap="login_github"></view>
				</view>
			</view> -->

			<!-- 底部信息 -->
			<view class="footer">
				<navigator url="./forget" open-type="navigate">找回密码</navigator>
				<text>|</text>
				<navigator url="./register" open-type="navigate">注册账号</navigator>
			</view>
		</view>
	</view>
</template>

<script>
	let _this;
	import wInput from '../../components/watch-login/watch-input.vue' //input
	import wButton from '../../components/watch-login/watch-button.vue' //button

	export default {
		data() {
			return {
				userName: '', //用户/电话
				passData: '', //密码
				isRotate: false, //是否加载旋转
				isFocus: true // 是否聚焦
			};
		},
		components: {
			wInput,
			wButton,
		},
		mounted() {
			_this = this;
			//this.isLogin();
		},
		methods: {
			isLogin() {
				//判断缓存中是否登录过，直接登录
				// try {
				// 	const value = uni.getStorageSync('setUserData');
				// 	if (value) {
				// 		//有登录信息
				// 		console.log("已登录用户：",value);
				// 		_this.$store.dispatch("setUserData",value); //存入状态
				// 		uni.reLaunch({
				// 			url: '../../../pages/index',
				// 		});
				// 	}
				// } catch (e) {
				// 	// error
				// }
			},
			startLogin(e) {
				console.log(e)
				//登录
				if (this.isRotate) {
					//判断是否加载中，避免重复点击请求
					return false;
				}
				if (this.userName.length == "") {
					this.$refs.uToast.show({
						title:'用户名不能为空',
						type: 'error',
						position: 'bottom',
					})
					return;
				}
				if (this.passData.length == "") {
					this.$refs.uToast.show({
						title: '密码不能为空',
						type: 'error',
						position: 'bottom',
					})
					return;
				}
				//请求接口
				this.$u.post('User/login', {
					username: this.userName,
					password: this.passData
				}).then(res => {
					if (res.status == 200) {
						console.log(res)
						uni.setStorage({
							key: 'token',
							data: res.data.token,
						});
						//写入vuex
						this.$store.commit('setUser',res.data.userinfo);
						this.$refs.uToast.show({
							title: '登录成功',
							type: 'success',
							position: 'bottom',
							callback() {
								uni.reLaunch({
									url: '/pages/chat/chatList',
								});
							}
						})
					} else {
						this.$refs.uToast.show({
							title: res.message,
							type: 'error',
							position: 'bottom',
						})

					}
				}).catch(e => {
					this.$refs.uToast.show({
						title: '服务器在开小差',
						type: 'warning',
						position: 'bottom',
					})
					console.log(e)
				})
				_this.isRotate = true
				setTimeout(function() {
					_this.isRotate = false
				}, 3000)

			},
			login_weixin() {
				//微信登录
				uni.showToast({
					icon: 'none',
					position: 'bottom',
					title: '...'
				});
			},
			login_weibo() {
				//微博登录
				uni.showToast({
					icon: 'none',
					position: 'bottom',
					title: '...'
				});
			},
			login_github() {
				//github登录
				uni.showToast({
					icon: 'none',
					position: 'bottom',
					title: '...'
				});
			}
		}
	}
</script>

<style>
	@import url("../../components/watch-login/css/icon.css");
	@import url("../../static/css/login/css/main.css");
</style>
