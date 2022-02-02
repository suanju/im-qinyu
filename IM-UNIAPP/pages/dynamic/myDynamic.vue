<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<u-modal v-model="delShow" :show-cancel-button="true" :show-title="false" content="是否删除此动态" @confirm="delOff()">
		</u-modal>
		<view>
			<u-navbar class="nvabar" :is-back="true" :is-fixed="true">
				<view class="nvabar-main">
					<!-- 导航内容体 -->
					<view class="nvabar-main-text">
						<text>我的动态</text>
					</view>
				</view>
			</u-navbar>
		</view>
		<Dynamic v-for="(item,index) in dynamicList" :key="item.id" :imgList="item.img" :avatar="item.avatar"
			:name="item.name" :publishTime="item.publishTime" :content="item.content" :isLike="item.isLike"
			:isGiveReward="item.isGiveReward" :likeNumber="item.likeNumber" :giveRewardNumber="item.giveRewardNumber"
			:chatNumber="item.chatNumber" @clickDynamic="clickDynamic(item.id)" @clickUser="clickUser(item.uid)"
			@clickThumbsup="clickThumbsup(item.id)" @clickGiveReward="clickGiveReward(item.id)"
			@clickChat="clickChat(item.id)" @delDynamic="delDynamic(item.id)">
		</Dynamic>
		<view class="content-null" v-if="list.length == 0">
			<image src="../../static/img/dynamic/dynamicNull.png"></image>
			<view class="content-null-text">快去发布动态吧!</view>
		</view>
	</view>
</template>

<script>
	import Dynamic from '../../components/qizai-dynamic/Dynamic.vue';
	import {datetime_to_unix} from '../../common/time.js';
	export default {
		components: {
			Dynamic
		},
		data() {
			return {
				delShow: false,
				delID: '',
				content: '是否删除此动态',
				list: [],
			}
		},
		mounted() {

		},
		onShow() {
			this.getDynamic();
		},
		methods: {
			clickDynamic(e) {
				console.log(e)
				uni.navigateTo({
					url: './dynamicInfo?id=' + e
				});
			},
			// 点击用户信息
			clickUser(e) {
				console.log(e);
				uni.navigateTo({
					url: '../personal/friendCard?id=' + e
				});
			},
			// 点赞
			clickThumbsup(e) {
				//点击后发送请求
				//获取当前动态是否点赞
				let state = this.list.filter((res) => {
					return res.id == e ? e : false;
				})
				state = !state[0].isLike;
				//点赞操作
				//本地取消
				if (state) {
					this.list = this.list.filter((res) => {
						if (res.id == e) {
							res.isLike = state;
							res.likeNumber++;
						}
						return e;
					})
				} else {
					this.list = this.list.filter((res) => {
						if (res.id == e) {
							res.isLike = state;
							res.likeNumber--;
						}
						return e;
					})
				}

				console.log(this.list);
				//请求服务器
				this.$u.post('UserDynamic/setDynamicLike', {
					id: e,
					state: state
				}).then(res => {
					console.log(res)
					if (res.status == 200) {


					} else {
						this.$refs.uToast.show({
							title: data.message,
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
			// 点击打赏
			clickGiveReward(e) {
				console.log(e);
				console.log('clickGiveReward');
			},
			// 点击聊天
			clickChat(e) {
				console.log(e);
				console.log('clickChat');
			},
			//添加动态
			addDynamic() {
				uni.navigateTo({
					url: './addDynamic'
				});
			},
			//点击删除
			delDynamic(e) {
				console.log(e)
				this.delID = e;
				this.delShow = !this.delShow;
			},
			//获取动态
			getDynamic() {
				this.$u.post('UserDynamic/getMyDynamic', {}).then(res => {
					uni.stopPullDownRefresh()
					if (res.status == 200) {
						let dynamic = res.data
						dynamic = dynamic.filter((e) => {
							e.img = Object.values(e.img)
							e.created_at = datetime_to_unix(e.created_at);
							for (let i = 0; i < e.img.length; i++) {
								e.img[i] = this.staticUrl + e.img[i];
							}
							//判断是否点赞
							if (!e.likes) {
								e.isLike = false;
								//点赞数量 
								e.likeNumber = 0;
							} else {
								e.isLike = Object.keys(e.likes).indexOf(String(this.$store.state.userInfo
									.id)) == -1 ? false : true;
								//点赞数量 
								e.likeNumber = Object.keys(e.likes).length;
							}
							e.comments == null ? e.chatNumber = 0 : e.chatNumber = e.comments.length
							e.publishTime = e.created_at
							return e;
						})

						this.list = dynamic
						console.log(this.list)

					} else {
						this.$refs.uToast.show({
							title: data.message,
							type: 'error',
							position: 'bottom',
						})
					}
				}).catch(e => {
					uni.stopPullDownRefresh()
					this.$refs.uToast.show({
						title: '服务器在开小差',
						type: 'warning',
						position: 'bottom',
					})
					console.log(e)
				})
			},
			//删除动态
			delOff() {
				this.$u.post('UserDynamic/delDynamic', {
					id: this.delID
				}).then(res => {
					if (res.status == 200) {
						this.getDynamic();
						this.$refs.uToast.show({
							title: '删除成功',
							type: 'success',
							position: 'bottom',
						})

					} else {
						this.$refs.uToast.show({
							title: data.message,
							type: 'error',
							position: 'bottom',
						})
					}
				}).catch(e => {
					uni.stopPullDownRefresh()
					this.$refs.uToast.show({
						title: '服务器在开小差',
						type: 'warning',
						position: 'bottom',
					})
					console.log(e)
				})
			}

		},
		computed: {
			dynamicList() {
				return this.list.sort((e, r) => {
					return r.created_at - e.created_at
				})
			}
		},
		onPullDownRefresh() {
			this.getDynamic();

		}
	}
</script>

<style lang="scss" scoped>
	.main {
		.content-null{
			width: 100%;
			text-align: center;
			.content-null-text{
				font-size: 16rpx;
				color: $uni-text-color-grey;
			}
		}
		.nvabar {

			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;

				.nvabar-text {
					padding: 12rpx;
					color: $uni-text-color;
					text-align: center;
				}

				.nvabar-main-text {
					flex: 1;
					padding: 12rpx;
					margin-right: 30rpx;
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
	}
</style>
