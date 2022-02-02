<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<u-navbar class="nvabar" :is-back="false" >
			<view class="nvabar-main">
				<!-- 导航内容体 -->
				<view class="nvabar-main-text">
					<text>群详情</text>
				</view>
			</view>
		</u-navbar>
		<view class="item">
			<u-swipe-action :index="index" v-for="(item,index) in ids" :key="index"
				@click="delItem(id,index)" :options="options">
				<view class="user-list">
					<u-avatar :src="staticUrl + item.portrait"></u-avatar>
					<view class="info">
						<view class="name">{{item.name}}</view>
					</view>
				</view>
			</u-swipe-action>
		</view>
	</view>
</template>

<script>
	export default{
		data(){
			return{
				options: [{
					text: '移除',
					style: {
						backgroundColor: '#FF0033'
					}
				}]
			}
		},
		props:['ids','id'],
		mounted() {
			
		},
		methods:{
			delItem(id,ids){
				this.$u.post('Group/delMember', {
					id:id,
					ids:ids
				}).then(res => {
					if (res.status == 200) {
						this.$emit('setIds',res.data)
						this.$refs.uToast.show({
							title: '移除成功',
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
			}
		}
	}
</script>

<style lang="scss" scoped>
	.main{
		/* 顶部导航栏 */
		.nvabar {
		
			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;
		
				.nvabar-main-text {
					flex: 1;
					padding: 12rpx;
					color: $uni-text-color;
					text-align: center;
				}
			}
		}
		.item {
			padding: 0 10;
		
			.user-list {
				height: 110rpx;
				width: 100%;
				padding-left: $uni-spacing-row-sm;
				display: flex;
				align-items: center;
				border-bottom: solid 1rpx $uni-bg-color;
		
				.info {
					padding-left: 16rpx;
				}
			}
		}
		
	}
</style>
