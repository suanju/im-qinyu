<template>
	<view class="main">
		<u-toast ref="uToast" />
		<u-navbar class="nvabar" :is-fixed="true">
			<view class="nvabar-main">
				<!-- 导航内容体 -->
				<view class="nvabar-main-text">
					<text>资料卡</text>
				</view>
			</view>
		</u-navbar>
		<view class="head">
			<view>
				<image class="head-img" src="../../static/img/home/friendCard.png"></image>
				<view class="head-body">
					<u-avatar class="nvabar-main-avatar" size="150" :src="staticUrl + userInfo.portrait">
					</u-avatar>
					<view class="head-body-data">
						<text class="head-body-data-name">{{userInfo.username}}</text>
						<view class="head-body-data-email">
							<text>邮箱:</text><text class="head-body-data-email-num">{{userInfo.email}}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="card">
			<view class="card-text">
				<u-icon class="card-text-icon" name="account-fill" size="30"></u-icon>用户资料
			</view>
			<view class="card-body">
				<view class="card-body-item" v-if="userInfo.signature"><text
						class="card-body-item-text">签名:</text>{{userInfo.signature}}</view>
				<view class="card-body-item" v-if="userInfo.hometown"><text
						class="card-body-item-text">家乡:</text>{{userInfo.hometown}}</view>
				<view class="card-body-item" v-if="userInfo.birthday"><text
						class="card-body-item-text">生日:</text>{{userInfo.birthday}}</view>
				<view v-if="!userInfo.signature && !userInfo.hometown && !userInfo.birthday " class="card-body-text">
					这家伙很懒,什么都没留下!
				</view>
			</view>
		</view>
		<view class="bottom">
			<u-button type="primary" v-if="userInfo.isFriend" @click="jumpSendMsg(userInfo.uid,userInfo.username)">发消息
			</u-button>
			<u-button type="primary" v-else @click="addFriend()">添加好友</u-button>
		</view>
		<u-popup v-model="addFriendShow" mode="bottom" border-radius="18" length="50%">
			<view class="fd-popup">
				<view class="fd-popup-haed">
					<u-avatar class="" size="160" :src="staticUrl +userInfo.portrait">
					</u-avatar>
					<view class="fd-popup-haed-text">
						<text>{{userInfo.username}}</text>
						<view class="fd-popup-haed-text-email">email: {{userInfo.email}}</view>
					</view>
				</view>
				<view class="fd-popup-text"><text>请填写验证信息</text>
					<u-input class="fd-popup-text-imput" v-model="addVerify" type="textarea" :border="true"
						:height="180" :auto-height="true" />
					<u-field v-model="addRemark" label="备注" placeholder="请填写备注">
					</u-field>
				</view>
				<u-button class="fd-popup-btn" size="default" @click="addFriendReq()">发送申请</u-button>
			</view>
		</u-popup>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				id: '',
				uid: '',
				userInfo: [],
				//添加好友需要
				addFriendShow: false,
				addVerify: '相遇即是缘', //验证信息
				addRemark: '',

			}
		},
		mounted() {
			this.getInfo();
		},
		onLoad: function(option) { //option为object类型，会序列化上个页面传递的参数
			this.id = option.id; //打印出上个页面传递的参数。
			this.uid = this.$store.state.userInfo.id;
		},
		methods: {
			getInfo() {
				console.log(this.uid)
				this.$u.post('UserInfo/getInfoById', {
					id: this.id,
					uid: this.uid
				}).then(res => {
					console.log(res)
					if (res.status == 200) {
						this.userInfo = res.data
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
			jumpSendMsg(fid, name) {
				uni.navigateTo({
					url: '../chat/chatRoom?fid=' + fid + '&fname=' + name
				});
			},
			addFriend() {
				this.addRemark = this.userInfo.username;
				this.addFriendShow = !this.addFriendShow;
			},
			//添加好友
			addFriendReq() {
				this.$u.post('User/addFriend', {
					username: this.userInfo.username,
					message: this.addVerify,
					remark: this.addRemark
				}).then(res => {
					if (res.status == 200) {
						this.$refs.uToast.show({
							title: res.message,
							type: 'success',
							position: 'bottom',
							callback: () => {
								this.addFriendShow = false;
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
			}
		}
	}
</script>
<style lang="scss" scoped>
	.main {

		/* 顶部导航栏 */
		.nvabar {

			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;

				.nvabar-main-text {
					flex: 1;
					padding-right: 34rpx;
					color: $uni-text-color;
					text-align: center;
				}
			}
		}

		.head {
			.head-img {
				width: 100%;
				height: 440rpx;
				z-index: -1;
			}

			.head-body {
				display: flex;
				padding-left: 44rpx;
				margin: -75rpx 0rpx 0rpx 0rpx;
				z-index: 99;

				.head-body-data {
					padding: 0rpx 0rpx 0rpx 40rpx;

					.head-body-data-name {
						color: $uni-text-color-white;
						font-size: 36rpx;
					}

					.head-body-data-email {
						padding-top: 25rpx;

						.head-body-data-email-num {
							padding-left: 8rpx;
						}
					}

				}
			}
		}

		.card {
			padding: 22rpx;

			.card-text {
				padding: 16rpx;

				.card-text-icon {
					padding-right: 8rpx;
				}
			}

			.card-body {
				box-shadow: 0 4px 8px 0 rgba(#007aff, 0.2);
				height: 100%;
				transition: 0.3s;
				width: 100%;
				border-radius: 5px;

				.card-body-item {
					height: 90rpx;
					width: 100%;
					line-height: 90rpx;
					margin-top: 30rpx;
					box-shadow: 0 4px 8px 0 rgba(#999, 0.2);

					.card-body-item-text {
						margin-left: 16rpx;
						padding-right: 8rpx;
					}
				}

				.card-body-text {
					height: 120rpx;
					text-align: center;
					line-height: 120rpx;

				}
			}
		}

		.bottom {
			position: fixed;
			bottom: 0;
			width: 100%;
			padding: 24rpx;
		}

		.fd-popup {
			padding: 12rpx $uni-spacing-row-sm 30rpx;

			.fd-popup-haed {
				display: flex;

				.fd-popup-haed-text {
					padding: 30rpx;
					font-size: 32rpx;

					.fd-popup-haed-text-email {
						font-size: 26rpx;
					}
				}
			}

			.fd-popup-text {
				color: $uni-text-color-grey;
				padding: 16rpx 0rpx 16rpx;

				.fd-popup-text-imput {
					margin-top: 16rpx;
				}
			}

			.fd-popup-btn {
				margin-top: 50rpx;
				color: $uni-color-primary;
			}

		}
	}
</style>
