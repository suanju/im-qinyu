<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<view class="head">
			<u-navbar class="nvabar" :is-fixed="true" title="创建群聊" title-color=" #007aff" title-size="26">
			</u-navbar>
		</view>
		<view class="middle">
			<u-field v-model="groupName" label="名称" placeholder="请填写群聊名称"></u-field>
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
		</view>
		<u-button class="btn" type="primary" @click="establish()">创建群聊</u-button>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				dataList: [],
				groupName: '',
				selectedIds: [],
			}
		},
		mounted() {

		},
		onLoad: function(option) {
			//option为object类型，会序列化上个页面传递的参数
			this.getlist(option.ids)

		},
		methods: {
			getlist(ids) {
				this.$u.post('User/friendArr', {}).then(res => {
					if (res.status == 200) {
						console.log(res)
						this.dataList = res.data.filter((e) => {
							if (e.fid == ids) {
								e.states = true
							}
							return e;
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
			establish() {
				//判断名字是否为空
				if (this.groupName == '') {
					this.$refs.uToast.show({
						title: '群名称不可为空',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				if (this.selectedIds.length < 2) {
					this.$refs.uToast.show({
						title: '群成员必须大于2人',
						type: 'error',
						position: 'bottom',
					})
					return false;
				}
				//发送请求
				this.$u.post('Group/crateGroup', {
					name: this.groupName,
					ids: this.selectedIds
				}).then(res => {
					console.log(res)
					if (res.status == 200) {
						this.$refs.uToast.show({
							title: '创建成功',
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
		}

		.btn {
			position: fixed;
			bottom: 10px;
			width: 94%;
			z-index: 999;
		}
	}
</style>
