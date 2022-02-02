<template>
	<view class="main">
		<u-toast ref="uToast" />
		<u-navbar class="nvabar" :is-fixed="true">
					<view class="nvabar-main">
						<!-- 导航内容体 -->
						<view class="nvabar-main-text">
							<text>群详情</text>
						</view>
					</view>
		</u-navbar>
		<u-action-sheet :list="[{text: '更换头像',color: '#007aff',fontSize: 24}]" @click="updatePortrait()"
			v-model="updataShow"></u-action-sheet>
		<u-picker mode="time" v-model="timeShow" @confirm="timeConfirm" :end-year="(new Date).getFullYear()"></u-picker>
		<view class="head">
			<view>
				<image class="head-img" src="../../static/img/home/dataCard.png"></image>
				<view class="head-body">
					<u-avatar @click="updataShow = true" class="nvabar-main-avatar" size="150"
						:src="staticUrl +$store.state.userInfo.portrait">
					</u-avatar>
					<view class="head-body-data">
						<text class="head-body-data-name">{{$store.state.userInfo.username}}</text>
						<view class="head-body-data-email">
							<text>邮箱:</text><text
								class="head-body-data-email-num">{{$store.state.userInfo.email}}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="card">
			<view class="card-text">
				<u-icon class="card-text-icon" name="account-fill" size="30"></u-icon>我的资料
				<u-button class="card-text-btn" size="mini" :ripple="true" @click="setUserInfo()">更新资料</u-button>
			</view>
			<view class="card-body">
				<view>
					<u-field v-model="userData.signature" label="签名 :" placeholder="请填写个性签名">
					</u-field>
					<u-field v-model="userData.hometown" label="家乡 :" placeholder="请填写家乡">
					</u-field>
					<u-field v-model="userData.birthday" label="生日 :" placeholder="请填写生日" @click="timeShow = true">
					</u-field>
				</view>
			</view>
		</view>
		<view>

		</view>
	</view>
</template>

<script>
	import {
		requestUrl
	} from '../../common/config.js';
	export default {
		data() {
			return {
				userData: {
					signature: '',
					hometown: '',
					birthday: '',
				},
				updataShow: false,
				timeShow: false,
			}
		},
		mounted() {
			this.getUserInfo();
		},
		methods: {
			//时间选择器回调
			timeConfirm(time) {
				console.log(time);
				this.userData.birthday = time.year + '-' + time.month + '-' + time.day
			},
			getUserInfo() {
				this.$u.post('UserInfo/getOneselfInfo', {}).then(res => {
					if (res.data == null) {
						return;
					} else {
						this.userData.signature = res.data.signature;
						this.userData.hometown = res.data.hometown;
						this.userData.birthday = res.data.birthday;
					}
				}).catch(e => {})
			},
			setUserInfo() {
				this.$u.post('UserInfo/setOneselfInfo', {
					signature: this.userData.signature,
					hometown: this.userData.hometown,
					birthday: this.userData.birthday,
				}).then(res => {
					if (res.data) {
						console.log(123)
						this.$refs.uToast.show({
							title: '更新成功',
							type: 'success',
							position: 'bottom',
						})
					}
				}).catch(e => {})

			},
			//更换头像
			updatePortrait() {
				//选择图片
				uni.chooseImage({
					count: 1, //默认9
					sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
					sourceType: ['album'], //从相册选择
					success: (res) => {
						let phopo = res.tempFilePaths[0];
						console.log(phopo);
						//上传图片
						uni.uploadFile({
							url: requestUrl +'/User/updatePortrait', 
							filePath: phopo,
							header: {
								'token': uni.getStorageSync('token')
							},
							name: 'file',
							success: (uploadFileRes) => {
								this.$refs.uToast.show({
									title: '更新成功',
									type: 'success',
									position: 'bottom',
								})
								let data = JSON.parse(uploadFileRes.data).data;
								let info = this.$store.state.userInfo
								info.portrait = data;
								this.$store.store.commit('setUser', info);
							},
							fail: (e) => {
								console.log(e)
							}
						});
					}
				});

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

				.card-text-btn {
					float: right;
					color: $uni-bg-color-grey;
					background-color: $uni-color-primary;

				}
			}

			.card-body {
				box-shadow: 0 4px 8px 0 rgba(#007aff, 0.2);
				height: 100%;
				transition: 0.3s;
				width: 100%;
				border-radius: 5px;
			}
		}
	}
</style>
