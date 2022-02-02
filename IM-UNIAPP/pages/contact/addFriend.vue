<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view>
			<u-navbar class="nvabar" :is-back="true" :is-fixed="true" title="添加" title-color="#007aff">
			</u-navbar>
		</view>
		<u-tabs inactive-color="#007aff" :list="tabsList" :is-scroll="false" :current="current" @change="change">
		</u-tabs>
		<view class="fd" v-if="current == 0">
			<view class="fd-head">
				<u-search placeholder="请输入好友昵称" v-model="friendKey" @custom="searchFriend" @search="searchFriend"
					:clearabled="true"></u-search>
			</view>
			<view class="fd-item" v-for="(item,id) in addFriendList" :key="id">
				<u-avatar class="fd-item-avatar" size="120" :src="staticUrl +item.portrait">
				</u-avatar>
				<view class="fd-item-text">
					<text>{{item.username}}</text>
					<view class="fd-item-text-email">email: {{item.email}}</view>
				</view>
				<view class="fd-item-btn">
					<u-button size="mini" @click="addFriend(item.id)">添加</u-button>
				</view>
			</view>
			<u-popup v-model="addFriendShow" mode="bottom" border-radius="18" length="50%">
				<view class="fd-popup">
					<view class="fd-popup-haed">
						<u-avatar class="" size="160" :src="staticUrl +targetFriend.portrait">
						</u-avatar>
						<view class="fd-popup-haed-text">
							<text>{{targetFriend.username}}</text>
							<view class="fd-popup-haed-text-email">email: {{targetFriend.email}}</view>
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


		<view class="fd" v-if="current == 1">
			<view class="fd-head">
				<u-search placeholder="请输入群聊名" v-model="groupKey" @custom="searchGroup" @search="searchGroup"
					:clearabled="true"></u-search>
			</view>
			<view class="fd-item" v-for="(item,id) in addGroupList" :key="id">
				<u-avatar class="fd-item-avatar" size="120" :src="staticUrl +item.portrait">
				</u-avatar>
				<view class="fd-item-text">
					<text>{{item.name}}</text>
					<view><text>人数 : {{item.idsKey.length}}</text></view>
				</view>
				<view class="fd-item-btn">
					<u-button size="mini" @click="addGroup(item.id)">加入</u-button>
				</view>
			</view>
			<u-popup v-model="addGroupShow" mode="bottom" border-radius="18" length="50%">
				<view class="fd-popup">
					<view class="fd-popup-haed">
						<u-avatar class="" size="160" :src="staticUrl +targetGroup.portrait">
						</u-avatar>
						<view class="fd-popup-haed-text">
							<text>{{targetGroup.name}}</text>
						</view>
					</view>
					<view class="text">群主信息</view>
					<view class="fd-popup-middle">
						<u-avatar class="" size="100" :src="staticUrl +targetGroup.rootInfo.portrait">
						</u-avatar>
						<view class="fd-popup-haed-text">
							<text>{{targetGroup.rootInfo.username}}</text>
						</view>
					</view>
					<view class="fd-popup-text"><text>请填写验证信息</text>
						<u-input class="fd-popup-text-imput" v-model="addGroupVerify" type="textarea" :border="true"
							:height="180" :auto-height="true" />
						</u-field>
					</view>
					<u-button class="fd-popup-btn" size="default" @click="addGroupReq()">发送申请</u-button>
				</view>
			</u-popup>
		</view>

	</view>
</template>


<script>
	export default {
		data() {
			return {
				tabsList: [{
					name: '找人'
				}, {
					name: '找群'
				}],
				current: 0,
				//添加好友需要
				friendKey: '',
				addFriendList: [],
				addFriendShow: false,
				targetFriend: [], //选择待添加的好友
				addVerify: '相遇即是缘', //验证信息
				addRemark: '',
				//添加群聊需要
				groupKey: '',
				addGroupList: [],
				addGroupShow: false,
				targetGroup: {
					'portrait': '占位',
					'rootInfo': {
						'portrait': '占位',
						'username': '占位'
					}
				}, //选择待添加的好友
				addGroupVerify: '一起来聊天才热闹！', //验证信息
			}
		},
		methods: {
			change(index) {
				this.current = index;
			},
			searchFriend(value) {
				if (value == '') {
					this.$refs.uToast.show({
						title: "请输入昵称",
						type: 'error',
						position: 'bottom',
					})
					return;
				}
				this.$u.post('User/searchFriend', {
					value
				}).then(res => {
					if (res.status == 200) {
						if (res.data == null) {
							this.$refs.uToast.show({
								title: "没有用户",
								type: 'error',
								position: 'bottom',
							})
						} else {
							//清空后添加
							this.addFriendList = [];
							this.addFriendList.push(res.data);
						}
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
			addFriend(id) {
				this.addFriendShow = true;
				let targetFriend = this.addFriendList.filter((data) => {
					return data.id = id
				})
				this.targetFriend = targetFriend[0];
				this.addRemark = targetFriend[0].username;
			},
			addFriendReq() {
				this.$u.post('User/addFriend', {
					username: this.targetFriend.username,
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
			},
			//群聊需要
			searchGroup(value) {
				if (value == '') {
					this.$refs.uToast.show({
						title: "请输入群名称",
						type: 'error',
						position: 'bottom',
					})
					return;
				}
				this.$u.post('Group/searchGroup', {
					value
				}).then(res => {
					if (res.status == 200) {
						if (res.data == null || res.data == []) {
							this.$refs.uToast.show({
								title: "没有群聊",
								type: 'error',
								position: 'bottom',
							})
						} else {
							//清空后添加
							this.addGroupList = [];
							let data = res.data
							console.log(data)
							data = data.filter((e) => {
								e.idsKey = Object.keys(e.ids);
								return e;
							})
							this.addGroupList = data;


						}
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
			addGroup(id) {
				this.addGroupShow = true;
				let targetGroup = this.addGroupList.filter((data) => {
					if (data.id == id) {
						return data;
					}
				})
				this.targetGroup = targetGroup[0];
			},
			addGroupReq() {
				this.$u.post('Group/addGroup', {
					name: this.targetGroup.name,
					message: this.addGroupVerify,
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
		padding: 0rpx 22rpx;

		.nvabar {
			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;
			}
		}

		.fd {
			.fd-head {
				padding: 12rpx $uni-spacing-row-sm 30rpx;
			}

			.fd-item {
				display: flex;
				border-radius: 18rpx;
				height: 150rpx;
				box-shadow: 0 4rpx 8rpx 0 rgba(#007aff, 0.2), 0 6rpx 20rpx 0 rgba(#007aff, 0.19);

				.fd-item-avatar {
					margin: 15rpx;
				}

				.fd-item-text {
					flex: 1;
					margin: 28rpx 8rpx 0rpx;

					.fd-item-text-email {
						color: $uni-text-color-grey;
						font-size: 22rpx;
					}
				}

				.fd-item-btn {
					margin-top: 50rpx;
					padding-right: 18rpx;
				}
			}

			.fd-popup {
				padding: 12rpx $uni-spacing-row-sm 30rpx;

				.text {
					width: 100%;
					font-size: 24rpx;
					color: $uni-text-color-grey;
					padding: 20rpx;
					border-bottom: solid 1rpx $uni-bg-color;
				}

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

				.fd-popup-middle {
					display: flex;

					.fd-popup-haed-text {
						padding: 30rpx;
						font-size: 18rpx;
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
					position: fixed;
					bottom: 0;
					width: 94%;
					margin-bottom: 20rpx;
					color: $uni-color-primary;
				}

			}
		}

	}
</style>
