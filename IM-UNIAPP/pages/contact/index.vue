<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<!-- 侧边资料卡 -->
		<u-popup v-model="sideCardShow" width="70%" border-radius="22" :closeable="true">
			<sideCard @modifyModalShow="modifyModalShow" />
		</u-popup>
		<u-modal v-model="modalShow" :show-cancel-button="true" :show-title="false" content="真的要离开了嘛" @confirm="logOff">
		</u-modal>
		<view>
			<u-navbar class="nvabar" :is-back="false" :is-fixed="true">
				<view class="nvabar-main">
					<!-- 导航内容体 -->
					<view>
						<u-avatar class="nvabar-main-avatar" size="mini"
							:src="staticUrl +$store.state.userInfo.portrait" @click="sideCardShow = true"></u-avatar>
					</view>
					<view class="nvabar-main-text">
						<text>联系人</text>
					</view>
					<view class="nvabar-main-icon">
						<u-icon class="icon-camera" name="man-add" size="36" @click="addFriend"></u-icon>
					</view>
				</view>
			</u-navbar>
		</view>
		<view class="search">
			<u-search placeholder="" v-model="keyword" :showAction="false"></u-search>
		</view>
		<u-tabs inactive-color="#007aff" :list="tabsList" :is-scroll="false" :current="current" @change="change">
		</u-tabs>
		<view class="head">
			<view class="options" @click="jumpNewFirend()">
				<view class="options-text">新朋友
					<text class="head-count" v-show="$store.state.newFriend.filter((e)=>{
							if(e.message.state == 0)return e; 
						}).length != 0">
						{{$store.state.newFriend.filter((e)=>{
							if(e.message.state == 0)return e; 
						}).length}}
					</text>
				</view>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view>
			<view class="options" @click="jumpNewGroup()">
				<view class="options-text">群通知
					<text class="head-count" v-show="$store.state.newGroup.filter((e)=>{
							if(e.message.state == 0)return e; 
						}).length != 0">
						{{$store.state.newGroup.filter((e)=>{
							if(e.message.state == 0)return e; 
						}).length}}
					</text>
				</view>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view>
		</view>
		<view v-if="current == 1">
			<view v-if="groupList.length != 0">
				<view class="item" v-for="(data,id) in groupList" :key="id"
					@click="jumpGroupChat(data.group_id,data.name,data.portrait)">
					<view class="user-list">
						<u-avatar :src="staticUrl + data.portrait"></u-avatar>
						<view class="info">
							<view class="name">{{data.name}}</view>
						</view>
					</view>
				</view>
			</view>
			<view class="contact-null" v-if="groupList.length == 0">
				<image src="../../static/img/contact/contactNull.png"></image>
				<view class="contact-null-text">快去添加群聊吧!</view>
			</view>
		</view>
		<view v-if="current == 0">
			<view v-if="dataList.length != 0">
				<u-index-list :scrollTop="scrollTop">
					<view v-for="(item, index) in indexList" :key="index">
						<u-index-anchor :index="item" />
						<view class="item" v-for="(data,id) in dataList[item]" :key="id"
							@click="jumpChat(data.fid,data.username,data.portrait)">
							<view class="user-list">
								<u-avatar :src="staticUrl + data.portrait"></u-avatar>
								<view class="info">
									<view class="name">{{data.username}}</view>
								</view>
							</view>
						</view>
					</view>
				</u-index-list> 
			</view>
			<view class="contact-null" v-if="dataList.length == 0">
				<image src="../../static/img/contact/contactNull.png"></image>
				<view class="contact-null-text">快去添加好友吧!</view>
			</view>

		</view>
		<u-tabbar :list="$store.state.tabbar"></u-tabbar>
	</view>

</template>

<script>
	import sideCard from '../../components/home/sideCard.vue';
	import {deepCopy} from '../../common/fun.js';
	export default {
		data() {
			return {
				keyword: '',
				scrollTop: 0,
				originalDataList: [], //原始好友数据
				dataList: [],
				originalGroupList: [], //原始群聊数据
				groupList: [],
				indexList: ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S",
					"T", "U", "V", "W", "X", "Y", "Z"
				], //索引列表
				sideCardShow: false,
				modalShow: false,
				//tabs分类
				tabsList: [{
					name: '好友'
				}, {
					name: '群聊'
				}],
				current: 0,
			}
		},
		components: {
			sideCard
		},
		onPageScroll(e) {
			this.scrollTop = e.scrollTop;
		},
		mounted() {

		},
		onShow() {
			this.getlist();
			this.getGroup();
		},
		methods: {
			//tabs切换
			change(index) {
				this.current = index;
				console.log(this.current)
			},
			getlist() {
				this.$u.post('User/friendList', {}).then(res => {
					if (res.status == 200) {
						console.log(res)
						//获取index
						this.indexList = Object.keys(res.data);
						this.dataList = res.data;
						this.originalDataList = deepCopy(res.data);
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
			//获取群聊信息
			getGroup() {
				this.$u.post('Group/getGroup', {}).then(res => {
					if (res.status == 200) {
						console.log(res)
						this.groupList = res.data;
						this.originalGroupList = res.data;
						console.log(this.groupList)
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
			jumpChat(fid, name, portrait) {
				console.log(fid)
				uni.navigateTo({
					url: '../chat/chatRoom?fid=' + fid + '&fname=' + name + '&fportrait=' + portrait
				});
			},
			//进去群消息
			jumpGroupChat(fid, name, portrait) {
				uni.navigateTo({
					url: '../group/chatGroupRoom?fid=' + fid + '&fname=' + name + '&fportrait=' + portrait
				});
			},
			//添加好友
			addFriend() {
				uni.navigateTo({
					url: './addFriend'
				});
			},
			//跳转新朋友页面
			jumpNewFirend() {
				//清空服务器新朋友消息
				this.$u.post('User/readNewFriend', {}).then(res => {}).catch(e => {
					console.log(e)
				})
				////清空本地新朋友消息
				let vuexTab = this.$store.state.tabbar
				vuexTab = vuexTab.filter((res) => {
					if (res.text == '联系人') {
						res.count = res.count - this.$store.state.newGroup.filter((e) => {
							if (e.state == 0) return e;
						}).length < 0 ? 0 : res.count - this.$store.state.newGroup.filter((e) => {
							if (e.state == 0) return e;
						}).length < 0;
					}
					return res;
				})
				this.$store.commit('setTabbar', vuexTab);
				//跳转
				uni.navigateTo({
					url: './newFriend'
				});
			},
			//跳转群消息
			jumpNewGroup() {
				//清空服务器新朋友消息
				this.$u.post('User/readNewGroup', {}).then(res => {}).catch(e => {
					console.log(e)
				})
				////清空本地新朋友消息
				let vuexTab = this.$store.state.tabbar
				vuexTab = vuexTab.filter((res) => {
					if (res.text == '联系人') {
						res.count = res.count - this.$store.state.newGroup.filter((e) => {
							if (e.state == 0) return e;
						}).length < 0 ? 0 : res.count - this.$store.state.newGroup.filter((e) => {
							if (e.state == 0) return e;
						}).length < 0;
					}
					return res;
				})
				this.$store.commit('setTabbar', vuexTab);
				//跳转
				uni.navigateTo({
					url: './newGroup'
				});
			},
			//给子组件修改提示框方法
			modifyModalShow() {
				this.sideCardShow = false;
				this.modalShow = true;
			},
		},
		watch: {
			keyword() {
				console.log(this.keyword);
				if (this.current == 0) {
					console.log(this.dataList)
					console.log(this.originalDataList)
					this.indexList = [];
					Object.keys(this.originalDataList).filter((e) => {
						let listK = this.originalDataList[e].filter((es) => {
							if (es.username.search(this.keyword) != -1) return es;
						})
						if (listK != []) {
							this.dataList[e] = listK
						}
						//重新得到idnex索引
						if (Object.keys(this.dataList[e]).length != 0) {
							this.indexList.push(e)
						}
					})
					console.log(this.dataList)
					console.log(this.originalDataList)
					console.log(this.indexList)
				}else{
					this.groupList =  this.originalGroupList.filter((e)=>{
						if (e.name.search(this.keyword) != -1) return e;
					})
				}
			}
		},
		onPullDownRefresh() {
			this.getlist();
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

		.search {
			padding: 12rpx $uni-spacing-row-sm;
		}

		.head {
			.options {
				height: 84rpx;
				width: 100%;
				padding-left: $uni-spacing-row-sm;
				border-bottom: solid 1rpx $uni-bg-color;
				display: flex;
				align-items: center;

				.options-text {
					flex: 1;
				}

				.head-count {
					float: right;
					font-size: 16rpx;
					width: 32rpx;
					height: 32rpx;
					margin-right: 18rpx;
					border-radius: 100%;
					text-align: center;
					background-color: $uni-color-error;
					color: $uni-text-color-white;

				}

				.options-icon {
					padding-right: $uni-spacing-row-sm;
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

		.user-null-text {
			color: $uni-color-primary;
			padding-top: 16rpx;
			text-align: center;
		}

		.contact-null {
			width: 100%;
			text-align: center;

			.contact-null-text {
				font-size: 16rpx;
				color: $uni-text-color-grey;
			}
		}

	}
</style>
