<template>
	<view class="forget">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view class="content">
			<!-- 主体 -->
			<view class="main">
				<view class="tips">若你忘记了密码，可在此重置新密码。</view>
				<wInput v-model="userName" type="text" placeholder="请输入昵称"></wInput>
				<wInput v-model="passData" type="password" maxlength="16" placeholder="请输入新密码" isShowPass></wInput>
				<wInput v-model="verCode" type="number" maxlength="6" placeholder="验证码" isShowCode codeText="获取重置码"
					setTime="60" ref="runCode" @setCode="getVerCode()"></wInput>
			</view>

			<wButton class="wbutton" text="重置密码" :rotate="isRotate" @click.native="startRePass()"></wButton>

		</view>
		<view class="footer">
						<navigator url="./login" open-type="navigate">立即登入</navigator>
						<text>|</text>
						<navigator url="./register" open-type="navigate">立即注册</navigator>
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
				userName: "", //昵称
				passData: "", //密码
				verCode: "", //验证码
				isRotate: false, //是否加载旋转
			}
		},
		components: {
			wInput,
			wButton
		},
		mounted() {
			_this = this;
		},
		methods: {
			getVerCode() {
				//获取验证码
				if (_this.userName.length == 0) {
					this.$refs.uToast.show({
						title: '请输入昵称',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				console.log("发送验证码成功")
				//发送验证码
				this.$u.post('User/verificationCodeReset', {
					username: this.userName,
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
			startRePass() {
				//重置密码
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
				if (this.passData.length < 6) {
					this.$refs.uToast.show({
						title: '密码长度需大于6',
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
				//请求接口
				this.$u.post('User/passwordReset ', {
					username: this.userName,
					email: this.email,
					password: this.passData,
					verCode: this.verCode
				}).then(res => {
					if (res.status == 200) {
						console.log(res)
						this.$refs.uToast.show({
							title: '重置成功',
							type: 'success',
							position: 'bottom',
							callback() {
								uni.redirectTo({
									url: 'login',
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
