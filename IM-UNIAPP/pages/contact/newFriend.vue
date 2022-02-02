<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view>
			<u-navbar class="nvabar" :is-back="true" :is-fixed="true" title="好友处理" title-color="#007aff">
			</u-navbar>
		</view>
		<view class="item" v-for="(item,id) in newFriendList" :key="item.from">
			<u-avatar class="item-avatar" size="120" :src="staticUrl +item.message.portrait">
			</u-avatar>
			<view class="item-text">
				<text>{{item.username}}</text>
				<view class="item-text-msg">验证信息: {{item.message.message}}</view>
			</view>
			<view class="item-btn">
				<u-button class="item-btn-success" size="mini" type="success" @click="operationFriend(item.from,1)">同意
				</u-button>
				<u-button size="mini" type="error" @click="operationFriend(item.from,0)">拒绝</u-button>
			</view>
		</view>
		<view class="contact-null" v-if="newFriendList.length== 0">
			<image src="../../static/img/contact/newFriendNull.png"></image>
			<view class="contact-null-text">还没有好友申请!</view>
		</view>
		</view>
</template>


<script>
	export default {
		data() {
			return {
				newFriendList: []
			}
		},
		mounted() {
			this.init();
		},
		methods: {
			init() {
				this.newFriendList = this.$store.state.newFriend;
			},
			operationFriend(uid, decision) {
				this.$u.post('User/handleFriend', {
					decision: decision,
					target: uid,
				}).then(res => {
					if (res.status == 200) {
						let data = this.newFriendList.filter((data) => {
							if (data.from != uid) return data
						})
						this.$store.commit('setNewFriend', data);
						this.newFriendList = data;
						this.$refs.uToast.show({
							title: res.message,
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
	.main {
		padding: 0rpx 22rpx;

		.nvabar {
			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;
			}
		}

		.item {
			margin-top: 22rpx;
			display: flex;
			border-radius: 18rpx;
			height: 150rpx;
			box-shadow: 0 4rpx 8rpx 0 rgba(#007aff, 0.2), 0 6rpx 20rpx 0 rgba(#007aff, 0.19);

			.item-avatar {
				margin: 15rpx;
			}

			.item-text {
				flex: 1;
				margin: 28rpx 8rpx 0rpx;

				.item-text-msg {
					color: $uni-text-color-grey;
					font-size: 22rpx;
				}
			}

			.item-btn {
				margin-top: 50rpx;
				padding-right: 18rpx;

				.item-btn-success {
					margin-right: 18rpx;
				}
			}
		}
		.contact-null{
			width: 100%;
			text-align: center;
			.contact-null-text{
				font-size: 16rpx;
				color: $uni-text-color-grey;
			}
		}
	}
</style>
