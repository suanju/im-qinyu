<template>
	<view class="register">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view class="content">
			<!-- 头部logo -->
			<view class="header">
				<image src="../../static/img/login/login.gif"></image>
			</view>
			<!-- 主体 -->
			<view class="main">
				<wInput v-model="userName" type="text" maxlength="10" placeholder="昵称"></wInput>
				<wInput v-model="email" type="text" placeholder="邮箱"></wInput>
				<wInput v-model="passData" type="password" maxlength="11" placeholder="登录密码" isShowPass></wInput>
				<wInput v-model="verCode" type="number" maxlength="6" placeholder="验证码" isShowCode ref="runCode"
					@setCode="getVerCode()"></wInput>

			</view>

			<wButton class="wbutton" text="注 册" :rotate="isRotate" @click.native="startReg()"></wButton>

			<!-- 底部信息 -->

			<view class="footer">
				<navigator url="./forget" open-type="navigate">找回密码</navigator>
				<text>|</text>
				<navigator url="./login" open-type="navigate">立即登入</navigator>
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
				userName: '', // 昵称
				email: '',
				passData: '', //密码
				verCode: "", //验证码
				isRotate: false, //是否加载旋转
			}
		},
		components: {
			wInput,
			wButton,
		},
		mounted() {
			_this = this;
		},
		methods: {
			getVerCode() {
				//获取验证码
				if (_this.email == "") {
					this.$refs.uToast.show({
						title: '请填写邮箱',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				if (_this.email != "" && !/^\w(\w*\.*)*@(\w+\.)+\w{2,4}$/.test(_this.email)) {
					this.$refs.uToast.show({
						title: '邮箱格式错误',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				console.log("发送验证码成功")
				//发送验证码
				this.$u.post('User/verificationCode', {
					email: this.email,
				}).then(res => {
					if (res.status == 200) {
						console.log(res)
						this.$refs.uToast.show({
							title: '发送验证码成功',
							type: 'success',
							position: 'bottom',
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



				this.$refs.runCode.$emit('runCode'); //触发倒计时（一般用于请求成功验证码后调用）
				setTimeout(function() {
					_this.$refs.runCode.$emit('runCode', 0); //假装模拟下需要 终止倒计时
				}, 60000)
			},
			startReg() {
				//注册
				if (this.isRotate) {
					//判断是否加载中，避免重复点击请求
					return false;
				}
				if (this.userName.length == 0) {
					this.$refs.uToast.show({
						title: '昵称不能为空',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				if (this.email.length == 0) {
					this.$refs.uToast.show({
						title: '邮箱不能为空',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				if (this.verCode.length == 0) {
					this.$refs.uToast.show({
						title: '验证码不能为空',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				if (this.passData.length == 0) {
					this.$refs.uToast.show({
						title: '密码不能为空',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				//请求接口
				this.$u.post('User/register', {
					username: this.userName,
					email: this.email,
					password: this.passData,
					verCode: this.verCode
				}).then(res => {
					if (res.status == 200) {
						console.log(res)
						this.$refs.uToast.show({
							title: '注册成功',
							type: 'success',
							position: 'bottom',
							callback() {
								uni.redirectTo({
									url: './login',
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
			}
		}
	}
</script>

<style>
	@import url("../../components/watch-login/css/icon.css");
	@import url("../../static/css/login/css/main.css");
</style>
