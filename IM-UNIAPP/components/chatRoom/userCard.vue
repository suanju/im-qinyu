<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view class="head">
			<u-avatar class="head-avatar" size="180" :src="staticUrl + portrait">
			</u-avatar>
			<view>昵称 : {{name}}</view>
		</view>
		
		<view class="btn">
			<u-button class="btn-on" type="primary" @click="jumpGroup(id)">创建群聊</u-button>
			<u-button class="btn-on"  type="error" @click="delUser(id)">删除好友</u-button>
		</view>
	</view>
</template>

<script>
	export default{
		data(){
			return {
				
			}
		},
		props:['id','name','portrait'],
		methods:{
			delUser(id){
				this.$u.post('User/delFriend', {
					id:id
				}).then(res => {
					console.log(res)
					if (res.status == 200) {
						this.$refs.uToast.show({
							title: '删除成功',
							type: 'success',
							position: 'bottom',
							callback() {
								uni.redirectTo({
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
			},
			jumpGroup(id){
				uni.navigateTo({
					url: '/pages/group/create?ids=' + id,
				});
			}
		} 
	}
</script>

<style lang="scss" scoped>
	.main{
		padding: 0 $uni-spacing-row-sm 0;
		background-image: url(../../static/img/chat/userCard.jpg);
		background-size: 100% 100%; 
		height: 100%;
		
		.head{
			text-align: center;
			padding-top: 60rpx;
			height: 70%;
		}
		.btn{
			.btn-on{
				margin: 16rpx;
			}
		}
	}
</style>
