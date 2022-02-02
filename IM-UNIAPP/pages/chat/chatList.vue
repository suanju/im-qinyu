<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<u-modal v-model="modalShow" :show-cancel-button="true" :show-title="false" content="真的要离开了嘛" @confirm="logOff">
		</u-modal>
		<view>
			<u-navbar class="nvabar" :is-back="nvabar.isBack" :is-fixed="nvabar.isFixed">
				<view class="nvabar-main">
					<!-- 导航内容体 -->
					<view>
						<u-avatar class="nvabar-main-avatar" size="mini"
							:src="staticUrl +$store.state.userInfo.portrait" @click="sideCardShow = true"></u-avatar>
					</view>
					<view class="nvabar-main-name">
						<text>{{$store.state.userInfo.username}}</text>
					</view>
					<view class="nvabar-main-icon">
						<u-icon class="icon-camera" name="camera" size="36"></u-icon>
						<u-icon class="icon-plus" name="plus" size="36"></u-icon>
					</view>
				</view>
			</u-navbar>
		</view>
		<view>
			<!-- 侧边资料卡 -->
			<u-popup v-model="sideCardShow" width="70%" border-radius="22" :closeable="true">
				<sideCard @modifyModalShow="modifyModalShow" />
			</u-popup>
		</view>
		<view class="search">
			<u-search placeholder="" v-model="keyword" :showAction="false"></u-search>
		</view>
		<view class="item">

			<u-swipe-action :index="index" v-for="(item,index) in sortMsgList" :key="index"
				@content-click="jumpChat(item.id,item.username,item.portrait,item.type)"
				@click="delMsg(item.id,item.type,item.username)" :options="options">
				<view class="message_list">
					<u-avatar :src="staticUrl +item.portrait"></u-avatar>
					<view class="info">
						<view class="name">{{item.username}}</view>
						<view class="message">{{item.message}}</view>
					</view>
					<view class="state">
						<view class="time">{{fmtTime(item.time)}}</view>
						<view>
							<view class="count" v-show="item.unread != 0">
								{{item.unread}}
							</view>
						</view>
					</view>
				</view>
			</u-swipe-action>
		</view>
		<view class="chat-null" v-if="msgList.length == 0">
			<image src="../../static/img/chat/chatNull.png"></image>
			<view class="chat-null-text">还没有人给你发消息呢!</view>
		</view>
		<u-tabbar :list="$store.state.tabbar"></u-tabbar>
	</view>
</template>

<script>
	import {
		formattingTime
	} from '../../common/time.js';
	import sideCard from '../../components/home/sideCard.vue';
	export default {
		data() {
			return {
				keyword: '',
				userInfo: [],
				originalMsgList: [], //原始数据
				msgList: [],
				nvabar: {
					"isBack": false,
					"isFixed": true,
				}, //顶部导航栏
				modalShow: false,
				sideCardShow: false,
				options: [{
					text: '删除',
					style: {
						backgroundColor: '#FF0033'
					}
				}],
				//关于心跳重连
				heartbeatTime: 60000,
			};
		},
		components: {
			sideCard
		},
		mounted() {
			this.link();
		},
		onShow() {
			this.getMsgList();
		},
		methods: {
			link() {
				uni.connectSocket({
					url: this.wsUrl + '?type=index' + '&token=' + uni.getStorageSync('token')
				});
				uni.onSocketOpen((res) => {
					console.log('WebSocket连接已打开！');
					//设置发送心跳
					this.sendHeartbeat();
				});
				uni.onSocketError((res) => {
					console.log('WebSocket连接打开失败，请检查！');
				});
				uni.onSocketMessage((res) => {
					let data = JSON.parse(res.data);
					if (data.status == 200) {
						switch (data.data.type) {
							case 'chat':
								this.getMsgList();
								break;
							case 'chatGroup':
								this.getMsgList();
								break;
							case 'addFriend':
								this.onWsFeiend(data.data);
								break;
							case 'addGroup':
								console.log('群消息')
								break;
								this.onWsGroup(data.data);
						}
					}
				});

			},
			//发送心跳
			sendHeartbeat() {
				let _this = this;
				setInterval(() => {
					uni.sendSocketMessage({
						data: JSON.stringify({
							'type': 'heartbeat',
							'message': 'send heartbeat',
						}),
						fail: function() {
							_this.$refs.uToast.show({
								title: '发送心跳失败',
								type: 'error',
								position: 'bottom',
							})
						}
					});
				}, this.heartbeatTime)
			},
			//收到好友请求时处理方法
			onWsFeiend(data) {
				//将新好友数据添加到vuex
				this.$store.commit('setNewFriend', data);
				let tabbar = this.$store.state.tabbar;
				if (data.message.state != 1) {
					tabbar = tabbar.filter((res) => {
						if (res.text == '联系人') {
							res.count++
						}
						return res;
					});
				}
				this.$store.commit('setTabbar', tabbar);
			},
			//收到群通知的消息
			onWsGroup(data) {
				//将新好友数据添加到vuex
				this.$store.commit('setNewGroup', data);
				console.log(123)
				let tabbar = this.$store.state.tabbar;
				if (data.message.state != 1) {
					tabbar = tabbar.filter((res) => {
						if (res.text == '联系人') {
							res.count++
						}
						return res;
					});
				}
				this.$store.commit('setTabbar', tabbar);
			},
			getMsgList() {
				this.$u.post('User/latelyChat', {}).then(res => {
					if (res.status == 200) {
						//将未读消息添加底部导航小圆点
						let unread = 0;
						for (let i = 0; i < res.data.length; i++) {
							unread += res.data[i].unread
						}
						console.log(unread);
						let tabbar = this.$store.state.tabbar;
						tabbar = tabbar.filter((res) => {
							if (res.text == '消息') {
								res.count = unread
							}
							return res;
						});
						this.$store.commit('setTabbar', tabbar);
						//同步data
						this.msgList = res.data;
						//给一个原始数据不得改变
						this.originalMsgList = res.data;
						console.log(this.msgList)
					} else {
						// this.$refs.uToast.show({
						// 	title: res.message,
						// 	type: 'error',
						// 	position: 'bottom',
						// })

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
			fmtTime(time) {
				return formattingTime(time);
			},
			jumpChat(fid, name, portrait, type) {
				//消息清0
				this.msgList = this.msgList.filter((res) => {
					if (res.username == name) {
						res.unread = 0
						console.log(res)
					}
					return res
				})
				if (type == 'friend') {
					uni.navigateTo({
						url: '../chat/chatRoom?fid=' + fid + '&fname=' + name + '&fportrait=' + portrait
					});
				} else {
					uni.navigateTo({
						url: '/pages/group/chatGroupRoom?fid=' + fid + '&fname=' + name + '&fportrait=' + portrait
					});
				}

			},
			//退出登入
			logOff() {
				uni.reLaunch({
					url: '/pages/login/login'
				});
			},
			//给子组件修改提示框方法
			modifyModalShow() {
				this.sideCardShow = false;
				this.modalShow = true;
			},
			//删除消息
			delMsg(id, type, name) {
				//消息清0
				this.msgList = this.msgList.filter((res) => {
					if (res.username == name) {
						res.unread = 0
						console.log(res)
					}
					return res
				})
				//底部导航重新统计
				//将未读消息添加底部导航小圆点
				let unread = 0;
				for (let i = 0; i < this.msgList.length; i++) {
					unread += this.msgList[i].unread
				}
				console.log(unread);
				let tabbar = this.$store.state.tabbar;
				tabbar = tabbar.filter((res) => {
					if (res.text == '消息') {
						res.count = unread
					}
					return res;
				});
				this.$store.commit('setTabbar', tabbar);
				if (type == 'friend') {
					//删除消息(好友)
					this.$u.post('User/delLatelyChat', {
						id: id
					}).then(res => {
						if (res.status == 200) {
							let list = this.msgList.filter((e) => {
								if (e.username != name) {
									return e;
								}
							})
							this.msgList = list;
							this.getMsgList();
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
					//群聊
					this.$u.post('User/delLatelyChatGroup', {
						id: id
					}).then(res => {
						if (res.status == 200) {
							let list = this.msgList.filter((e) => {
								if (e.username != name) {
									return e;
								}
							})
							this.msgList = list;
							this.getMsgList();
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
		},
		computed: {
			sortMsgList() {
				if (this.msgList.length != 0) {
					return this.msgList.sort((before, after) => {
						return after.time - before.time
					})
				} else {
					return this.msgList;
				}

			}
		},
		//监听
		watch: {
			keyword() {
				this.msgList = this.originalMsgList.filter((e) => {
					if (e.username.search(this.keyword) != -1) return e;
				})
			}
		},
		onPullDownRefresh() {
			this.getMsgList();
			setTimeout(() => {
				uni.stopPullDownRefresh()
			}, 500)

		}
	}
</script>
<style lang="scss" scoped>
	.main {
		.nvabar {

			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;

				.nvabar-main-name {
					flex: 1;
					padding: 12rpx;
					color: $uni-text-color;
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

		.search {
			padding: 12rpx $uni-spacing-row-sm;

		}

		.message_list {
			height: 110rpx;
			width: 100%;
			padding-left: $uni-spacing-row-sm;
			display: flex;
			align-items: center;
			border-bottom: solid 1rpx $uni-bg-color;

			.info {
				padding-left: 16rpx;
				flex: 1;

				.name {
					font-size: 28rpx;
				}

				.message {
					color: $uni-text-color-grey;
					font-size: 22rpx;
					display: -webkit-box;
					-webkit-box-orient: vertical;
					-webkit-line-clamp: 3;
					overflow: hidden;
				}
			}

			.state {
				padding-right: $uni-spacing-row-sm;
				float: right;
				text-align: center;

				.time {
					color: $uni-text-color-grey;
					font-size: 22rpx;
				}

				.count {
					float: right;
					font-size: 16rpx;
					width: 32rpx;
					height: 32rpx;
					border-radius: 100%;
					background-color: $uni-color-error;
					color: $uni-text-color-white;

				}
			}
		}

		.chat-null {
			width: 100%;
			text-align: center;

			.chat-null-text {
				font-size: 16rpx;
				color: $uni-text-color-grey;
			}
		}
	}
</style>
