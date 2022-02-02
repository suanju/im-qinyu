<template>
	<view class="main">
		<u-toast ref="uToast" />
		<u-navbar class="nvabar" :is-fixed="true">
			<view class="nvabar-main">
				<!-- 导航内容体 -->
				<view class="nvabar-main-text">
					<text>群详情</text>
				</view>
				<view class="nvabar-main-icon">
					<u-icon class="icon-camera" name="list" size="36" @click="getDelGroup"></u-icon>
				</view>
			</view>
		</u-navbar>
		<u-action-sheet :list="sheet" @click="delGroup()" v-model="delShow">
		</u-action-sheet>
		<u-action-sheet :list="[{text: '更换头像',color: '#007aff',fontSize: 24}]" @click="updatePortrait()"
			v-model="updataShow"></u-action-sheet>
		<u-picker mode="time" v-model="timeShow" @confirm="timeConfirm" :end-year="(new Date).getFullYear()"></u-picker>
		<u-popup v-model="invitationShow" mode="bottom" border-radius="34" length="70%" :closeable="true">
			<Invitation :ids="data.idsKey" :id="data.id"></Invitation>
		</u-popup>
		<u-popup v-model="delMemberShow" mode="bottom" border-radius="34" length="70%" :closeable="true">
			<DelMember :ids="data.ids" :id="data.id" @setIds="setIds"></DelMember>
		</u-popup>
		<view class="head">
			<view>
				<image class="head-img" src="../../static/img/home/dataCard.png"></image>
				<view class="head-body">
					<u-avatar @click="setUpPorait" class="nvabar-main-avatar" size="150"
						:src="staticUrl + data.portrait">
					</u-avatar>
					<view class="head-body-data">
						<text class="head-body-data-name">{{data.name}}</text>
						<view class="head-body-data-email">
							<text>群号:</text><text class="head-body-data-email-num">{{data.id}}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="card">
			<view class="card-text">
				<u-icon class="card-text-icon" name="account-fill" size="30"></u-icon>群成员
				<u-button class="card-text-btn" size="mini" v-if="rootId == $store.state.userInfo.id" :ripple="true"
					@click="getInvitation()">邀请好友</u-button>
			</view>
			<view class="card-body member" @click="delMember()">
				<view class="member-item" v-for="(info,idnex) in data.ids" :key="info.name">
					<u-avatar class="nvabar-main-avatar" size="80" :src="staticUrl + info.portrait">
					</u-avatar>
				</view>
			</view>
		</view>
		<view class="card">
			<view class="card-text">
				<u-icon class="card-text-icon" name="account-fill" size="30"></u-icon>群资料
				<u-button class="card-text-btn" size="mini" v-if="rootId == $store.state.userInfo.id" :ripple="true"
					@click="setGroupInfo()">更新资料</u-button>
			</view>
			<view class="card-body">
				<view>
					<u-field v-model="data.manifesto" label="群宣言 :" placeholder="请填写群宣言">
					</u-field>
				</view>
				<view class="card-body-info">
					<view class="item">我的职位 : {{data.position}}</view>
					<view class="item">创建时间 : {{timestampFormat(data.created_at)}}</view>
				</view>
			</view>
		</view>
		<view>
		</view>
		<view class="bottom">
			<u-button type="primary" @click="jumpSendMsg(userInfo.uid,userInfo.username)">发消息</u-button>
		</view>
	</view>
</template>

<script>
	import {
		timestampFormat
	} from '../../common/time.js';
	import Invitation from '../../components/group/invitation.vue';
	import DelMember from '../../components/group/delMember.vue';
	import {
		requestUrl
	} from '../../common/config.js';
	export default {
		data() {
			return {
				id: '',
				rootId: '',
				groupId: '',
				data: {},
				updataShow: false,
				delShow: false,
				timeShow: false,
				invitationShow: false,
				delMemberShow: false,
				sheet: []
			}
		},
		components: {
			Invitation,
			DelMember
		},
		mounted() {
			this.getInfo();
		},
		onLoad: function(option) {
			//option为object类型，会序列化上个页面传递的参数
			this.id = option.id;
		},
		methods: {
			//时间选择器回调
			timeConfirm(time) {
				console.log(time);
				this.userData.birthday = time.year + '-' + time.month + '-' + time.day
			},
			getInfo() {
				this.$u.post('Group/getGroupInfo', {
					id: this.id
				}).then(res => {
					console.log(res)
					res.data.idsKey = Object.keys(res.data.ids);
					//获取群主id
					for (let i in res.data.ids) {
						console.log(res.data.ids[i])
						if (res.data.ids[i].type == 'root') {
							this.rootId = i;
						}
					}
					if (this.rootId == this.$store.state.userInfo.id) {
						this.sheet = [{
							text: '解散群聊',
							color: '#FF3300',
							fontSize: 24
						}];
					} else {

						this.sheet = [{
							text: '退出群聊',
							color: '#FF3300',
							fontSize: 24
						}];
					}
					this.data = res.data;
				}).catch(e => {})
			},
			setGroupInfo() {
				this.$u.post('Group/setManifesto', {
					id: this.data.id,
					manifesto: this.data.manifesto,
				}).then(res => {
					if (res.status == 200) {
						this.$refs.uToast.show({
							title: '更新成功',
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
				}).catch(e => {})

			},
			timestampFormat(e) {
				return timestampFormat(e);
			},
			//邀请好友
			getInvitation() {
				this.invitationShow = true;
			},
			//踢出群聊
			delMember() {
				this.delMemberShow = true;
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
							url: requestUrl + '/Group/updatePortrait',
							filePath: phopo,
							header: {
								'token': uni.getStorageSync('token'),
								'groupId': this.id
							},
							name: 'file',
							success: (uploadFileRes) => {
								this.$refs.uToast.show({
									title: '更新成功',
									type: 'success',
									position: 'bottom',
								})
								let data = JSON.parse(uploadFileRes.data).data;
								this.data.portrait = data;
							},
							fail: (e) => {
								console.log(e)
							}
						});
					}
				});

			},
			getDelGroup() {
				this.delShow = true;
			},
			setUpPorait() {
				if (this.rootId == this.$store.state.userInfo.id) this.updataShow = true
			},
			//解散群（退出群）
			delGroup() {
				if (this.rootId == this.$store.state.userInfo.id) {
					//为群主解散群
					this.$u.post('Group/delGroup', {
						id: this.id
					}).then(res => {
						if (res.status == 200) {
							this.$refs.uToast.show({
								title: '解散成功',
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
				} else {
					//为成员退出群
					this.$u.post('Group/exitGroup', {
						id: this.id
					}).then(res => {
						if (res.status == 200) {
							this.$refs.uToast.show({
								title: '退出成功',
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
				}

			},
			//给子组件的方法
			setIds(ids) {
				console.log(ids)
				this.data.ids = ids;
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
					padding: 12rpx;
					color: $uni-text-color;
					text-align: center;
				}

				.nvabar-main-icon {
					padding: 12rpx;
					color: $uni-text-color;

					.icon-camera {
						padding-right: 12rpx;
					}
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
			overflow: hidden;

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

				.card-body-info {
					padding-left: 14rpx;

					.item {
						padding: 14rpx;
					}

				}
			}

			.member {
				display: flex;
				height: 90rpx;

				.member-item {
					padding-left: 12rpx;
				}
			}
		}

		.bottom {
			position: fixed;
			bottom: 0;
			width: 100%;
			padding: 24rpx;
		}

	}
</style>
