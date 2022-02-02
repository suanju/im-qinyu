<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view class="middle">
			<u-checkbox-group @change="checkboxGroupChange" class="item" :wrap="true" width="100%">
				<u-checkbox @change="checkboxChange" v-model="data.states" v-for="(data,id) in dataList" :key="id"
					:name="data.fid" class="checkbox">
					<view class="user-list">
						<u-avatar :src="staticUrl + data.portrait"></u-avatar>
						<view class="info">
							<view class="name">{{data.username}}</view>
						</view>
					</view>
				</u-checkbox>
			</u-checkbox-group>
			<view class="contact-null" v-if="dataList.length == 0">
				<image src="../../static/img/contact/newFriendNull.png"></image>
				<view class="contact-null-text">没有可邀请的好友呢!</view>
			</view>
		</view>
		<u-button class="btn" type="primary" @click="setInvitation()">邀请</u-button>

	</view>
</template>

<script>
	export default {
		props: ['ids','id'],
		data() {
			return {
				dataList: [],
				selectedIds: [],
			}
		},
		mounted() {
			this.getlist();
		},
		methods: {
			getlist(ids) {
				this.$u.post('User/friendArr', {}).then(res => {
					if (res.status == 200) {
						console.log(res)
						console.log(this.ids)
						this.dataList = res.data.filter((e) => {
							if (this.ids.indexOf(String(e.fid)) == -1) {
								return e;
							}
						})
						console.log(this.dataList)
						// this.dataList = res.data;
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
			// 选中某个复选框时，由checkbox时触发
			checkboxChange(e) {
				// console.log(e);
			},
			// 选中任一checkbox时，由checkbox-group触发
			checkboxGroupChange(e) {
				this.selectedIds = e;
			},
			//创建群聊
			setInvitation() {
				if (this.selectedIds.length < 1) {
					this.$refs.uToast.show({
						title: '未选中',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				//发送请求
				this.$u.post('Group/invitationGroup', {
					id: this.id,
					ids: this.selectedIds
				}).then(res => {
					console.log(res)
					if (res.status == 200) {
						this.$refs.uToast.show({
							title: '邀请成功',
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
		padding: 0 $uni-spacing-row-sm 0 $uni-spacing-row-sm;

		.middle {
			.item {
				padding: 0 10;

				// width: 100%;
				.checkbox {
					// width: 100%;

					.user-list {
						height: 110rpx;
						width: 999rpx;
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

			.contact-null {
				width: 100%;
				height: 500rpx;
				text-align: center;

				.contact-null-text {
					font-size: 16rpx;
					color: $uni-text-color-grey;
				}
			}
		}

		.btn {
			position: fixed;
			bottom: 10px;
			width: 94%;
			z-index: 999;
		}
	}
</style>
