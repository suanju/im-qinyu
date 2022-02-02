<template>
	<view class="main">
		<view class="content-null" v-if="collect.length == 0">
			<image src="../../static/img/collect/collectNull.png"></image>
		</view>
		<view class="collect">
			<view class="collect-item" v-for="(item,idnex) in collectList" :key="item.id" @click="sendCollect(item.id)">
				<view class="collect-item-head">
					<u-avatar class="collect-item-avatar" size="120" :src="item.portrait">
					</u-avatar>
					<view class="collect-item-text">
						<view class="collect-item-text-name">发送者 : {{item.username}}
							<view class="collect-item-text-time">{{fmtTime(item.created_at)}}</view>
						</view>
						<view class="collect-item-text-collect" v-if="item.collect_type == 'text'">{{item.collect}}
						</view>
						<view v-if="item.collect_type == 'pic'"><img class="collect-item-pic-collect"
								:src="item.collect"></img></view>
						<view class="collect-item-mic-collect" :style="{width:contentMicWidth(item.sec)}"
							v-if="item.collect_type == 'mic' " @click="playMic(item.collect)">
							<view class="collect-item-mic-collect-count">
								<u-icon class="content-icon" name="play-circle" size="36"></u-icon>
								<text v-if="item.min">{{item.min}}′</text><text>{{item.sec}} ″</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="collect-text">可以在聊天中长按消息进行收藏哟!</view>
		</view>
	</view>
</template>

<script>
	import {
		formattingTime
	} from '../../common/time.js';
	export default {
		data() {
			return {
				collect: [],
				collectID: '',
				collectShow: false

			}
		},
		mounted() {
			this.getCollect();
		},
		methods: {
			//格式化时间
			fmtTime(time) {
				return formattingTime(time);
			},
			getCollect() {
				this.$u.post('collect/getCollect', {}).then(res => {
					if (res.status == 200) {
						console.log(res.data)
						let data = res.data.filter(e => {
							e.portrait = this.staticUrl + e.portrait
							if (e.collect_type == 'pic') {
								let data = JSON.parse(e.collect)
								e.collect = this.staticUrl + data.path;
							}
							if (e.collect_type == 'mic') {
								let data = JSON.parse(e.collect)
								e.collect = this.staticUrl + data.base64
								e.min = data.time.min
								e.sec = data.time.sec
							}
							return e;
						})
						this.collect = data;
						console.log(this.collect);
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
			//计算语音长度
			contentMicWidth(e) {
				return 120 + e * 5 + 'rpx';
			},
			//点击语音消息播放语音
			playMic(src) {
				console.log(src)
				const innerAudioContext = uni.createInnerAudioContext();
				innerAudioContext.autoplay = true;
				innerAudioContext.src = src;
				innerAudioContext.onPlay(() => {
					console.log('开始播放');
				});
				innerAudioContext.onError((res) => {
					console.log(res);
				});
			},
			sendCollect(id) {
				let data = this.collect.filter((e) => {
					if (e.id == id) {
						return e
					}
				})
				console.log(data)
				this.$emit('sendCollect', data, 'collect')
			}
		},
		computed: {
			collectList() {
				return this.collect.sort((f, q) => {
					return q.created_at - f.created_at
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.main {
		padding: 0rpx 22rpx;

		.content-null {
			width: 100%;
			text-align: center;
		}

		.collect {

			.collect-text {
				padding-top: 16rpx;
				text-align: center;
				font-size: 16rpx;
				color: $uni-text-color-grey;
			}

			.collect-item {
				width: 100%;
				padding-left: $uni-spacing-row-sm;
				align-items: center;
				border-bottom: solid 1rpx $uni-bg-color;
				box-shadow: 0 4rpx 8rpx 0 rgba(#007aff, 0.2), 0 6rpx 20rpx 0 rgba(#007aff, 0.19);

				.collect-item-head {
					display: flex;

					.collect-item-avatar {
						margin: 15rpx;
					}

					.collect-item-text {
						flex: 1;
						margin: 28rpx 8rpx 0rpx;

						.collect-item-text-name {
							// float: left;

						}

						.collect-item-text-time {
							float: right;
							font-size: 16rpx;
							color: $uni-text-color-grey;
						}

						.collect-item-pic-collect {
							padding-top: 14rpx;
							border-radius: 30rpx;
							max-width: 85%;
							max-height: 400rpx;

						}

						.collect-item-mic-collect {
							min-width: 80rpx;
							height: 35px;
							color: #fff;
							position: relative;
							margin: 9rpx;
							background: $uni-color-primary;
							border-radius: 5px;

							.collect-item-mic-collect-count {
								display: flex;
								padding-left: 12rpx;
								padding-top: 16rpx;
								margin-right: 10rpx;

								.content-icon {
									flex: 1;
								}

							}
						}

						.collect-item-mic-collect:after {
							content: '';
							position: absolute;
							top: 10px;
							left: -4px;
							width: 8px;
							height: 8px;
							transform: rotate(45deg);
							background-color: $uni-color-primary;
						}

					}

				}


			}
		}
	}
</style>
