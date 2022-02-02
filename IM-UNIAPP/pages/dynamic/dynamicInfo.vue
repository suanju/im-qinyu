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
						<text>动态详情</text>
					</view>
				</view>
			</u-navbar>
		</view>
		<view class="dynamic">
			<view class="user__container">
				<view class="user__header-warp">
					<!-- 头像组 -->
					<view class="user__header" @click.stop="clickUser(dynamicInfo.uid)">
						<image class="user__header-image" :src="staticUrl + dynamicInfo.avatar" mode="aspectFill">
						</image>
					</view>
				</view>
				<view class="user__content">
					<view class="user__content-main">
						<text class="user__content-title uni-ellipsis"
							@click.stop="clickUser(dynamicInfo.uid)">{{dynamicInfo.name}}</text>
						<text
							class="user__content-note uni-ellipsis">{{timestampFormat(dynamicInfo.publishTime) }}</text>
					</view>
					<view @click.stop="delShow = true">
						<u-icon v-if="dynamicInfo.name == $store.state.userInfo.username" name="close" color="red"
							size="34"></u-icon>
					</view>

				</view>
			</view>
			<view class="text">{{dynamicInfo.content}}</view>
			<view class="allImage">
				<view class="imgList">
					<view class="images" v-for="(item,index) in dynamicInfo.img" :key="index">
						<image @click.stop="previewImg()" class="oneimg" :src="item" mode="aspectFill"
							:style="{width:imgWidth+'px','max-height':imgHeight+'px'}"></image>
					</view>
				</view>
			</view>
			<view class="operate" :style="'width: 100%;display:'+operateDisplay">
				<uni-grid :column="3" :showBorder="false" :square="false">
					<uni-grid-item>
						<span :style="'color:'+thumbsupColor" @click.stop="clickThumbsup()">
							<u-icon name="thumb-up" size="28" :style="'margin-right: 2px;color:'+thumbsupColor">
							</u-icon>
							{{dynamicInfo.likeNumber?dynamicInfo.likeNumber:'点赞'}}
						</span>
					</uni-grid-item>
				</uni-grid>
			</view>
			<view class="bottom-line"></view>
		</view>
		<view class="comments-list">
			<view class="comments-list-item">
				<view class="message_list" v-for="(item,index) in dynamicInfo.comments" :key="index">
					<u-avatar :src="staticUrl + item.avatar">
					</u-avatar>
					<view class="info">
						<view class="name">{{item.name}}</view>
						<view class="message">{{item.text}}</view>
					</view>
					<view class="state">
						<view class="time">{{timestampFormat(item.time)}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="comments">
			<u-input v-model="commentsText" @focus="commentsTextFocus" :focus="false" @blur="commentsTextBlur"
				:border="true" border-color="#007aff" />
			<u-button type="primary" size="medium" shape="square" class="comments-btn" @click="sendComments()"
				:style="'display:'+ inputBtn">发布</u-button>
		</view>
	</view>
</template>

<script>
		import {datetime_to_unix} from '../../common/time.js';
	export default {
		data() {
			return {
				dynamicId: '',
				delShow: false,
				dynamicInfo: [],
				commentsText: '',
				//组件需要
				windowWidth: 0, //屏幕可用宽度
				windowHeight: 0, //屏幕可用高度
				imgWidth: 0, //图片宽度
				imgHeight: 0, //图片高度
				thumbsupColor: 'gray',
				heartColor: 'gray',
				userDisplay: 'block',
				operateDisplay: 'block',
				//底部输入框
				inputBtn: 'none'
			}
		},
		onLoad: function(option) { //option为object类型，会序列化上个页面传递的参数
			this.dynamicId = option.id; //打印出上个页面传递的参数。
		},
		mounted() {
			this.getinfo();
		},
		updated() {
			this.initOperate();
		},
		methods: {
			inIt() {
				const res = uni.getSystemInfoSync();
				this.windowHeight = res.windowHeight;
				this.windowWidth = res.windowWidth;
				if (this.userNoShow) {
					this.userDisplay = 'none';
				}
				console.log(this.operateNoShow);
				if (this.operateNoShow) {
					this.operateDisplay = 'none';
				}
				this.judgeImg();
				this.initOperate();
			},
			clickUser(e) {
				uni.navigateTo({
					url: '../personal/friendCard?id=' + e
				});
			},
			getinfo() {
				this.$u.post('UserDynamic/getDynamicById', {
					dynamicId: this.dynamicId
				}).then(res => {
					uni.stopPullDownRefresh()
					if (res.status == 200) {
						let dynamic = res.data
						dynamic.img = Object.values(dynamic.img)
						for (let i = 0; i < dynamic.img.length; i++) {
							dynamic.img[i] = this.staticUrl + dynamic.img[i];
						}
						//判断是否点赞
						if (!dynamic.likes) {
							dynamic.isLike = false;
							//点赞数量 
							dynamic.likeNumber = 0;
						} else {
							dynamic.isLike = Object.keys(dynamic.likes).indexOf(String(this.$store.state.userInfo
								.id)) == -1 ? false : true;
							//点赞数量 
							dynamic.likeNumber = Object.keys(dynamic.likes).length;
						}
						dynamic.chatNumber == null ? 0 : Object.keys(dynamic.chatNumber).length
						dynamic.publishTime = datetime_to_unix(dynamic.created_at)
						this.dynamicInfo = dynamic;
						console.log(this.dynamicInfo)
						this.inIt()

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
			// 预览图片
			previewImg() {
				uni.previewImage({
					urls: this.dynamicInfo.img,
					longPressActions: {
						itemList: ['保存图片'],

					}
				})
			},
			initOperate() {
				if (this.dynamicInfo.isLike) {
					this.thumbsupColor = '#fb5f5f'
				} else {
					this.thumbsupColor = '#999';
				};
			},
			// 自适应判断
			judgeImg() {
				if (this.dynamicInfo.img.length == 1) {
					this.imgWidth = this.windowWidth * 2 / 3;
					this.imgHeight = this.windowHeight * 3 / 5;
				} else if (this.dynamicInfo.img.length == 4) {
					this.imgWidth = this.windowWidth / 3.3;
					this.imgHeight = this.imgWidth;
				} else {
					this.imgWidth = this.windowWidth / 3.4;
					this.imgHeight = this.imgWidth;
				}
			},
			timestampFormat(timestamp) {
				console.log(timestamp)
				if (!timestamp) return '';

				function zeroize(num) {
					return (String(num).length == 1 ? '0' : '') + num;
				}

				var curTimestamp = parseInt(new Date().getTime() / 1000); //当前时间戳
				var timestampDiff = curTimestamp - timestamp; // 参数时间戳与当前时间戳相差秒数

				var curDate = new Date(curTimestamp * 1000); // 当前时间日期对象
				var tmDate = new Date(timestamp * 1000); // 参数时间戳转换成的日期对象

				var Y = tmDate.getFullYear(),
					m = tmDate.getMonth() + 1,
					d = tmDate.getDate();
				var H = tmDate.getHours(),
					i = tmDate.getMinutes(),
					s = tmDate.getSeconds();

				if (timestampDiff < 60) { // 一分钟以内
					return "刚刚";
				} else if (timestampDiff < 3600) { // 一小时前之内
					return Math.floor(timestampDiff / 60) + "分钟前";
				} else if (curDate.getFullYear() == Y && curDate.getMonth() + 1 == m && curDate.getDate() == d) {
					return '今天' + zeroize(H) + ':' + zeroize(i);
				} else {
					var newDate = new Date((curTimestamp - 86400) * 1000); // 参数中的时间戳加一天转换成的日期对象
					if (newDate.getFullYear() == Y && newDate.getMonth() + 1 == m && newDate.getDate() == d) {
						return '昨天' + zeroize(H) + ':' + zeroize(i);
					} else if (curDate.getFullYear() == Y) {
						return zeroize(m) + '月' + zeroize(d) + '日 ' + zeroize(H) + ':' + zeroize(i);
					} else {
						return Y + '年' + zeroize(m) + '月' + zeroize(d) + '日 ' + zeroize(H) + ':' + zeroize(i);
					}
				}
			},
			//点赞动态
			clickThumbsup() {
				//点击后发送请求
				//获取当前动态是否点赞
				let state = !this.dynamicInfo.isLike;
				//点赞操作
				//本地取消
				if (state) {
					this.dynamicInfo.isLike = state;
					this.dynamicInfo.likeNumber++;
				} else {
					this.dynamicInfo.isLike = state;
					this.dynamicInfo.likeNumber--;
				}
				console.log(this.dynamicInfo);
				//请求服务器
				this.$u.post('UserDynamic/setDynamicLike', {
					id: this.dynamicInfo.id,
					state: state
				}).then(res => {
					console.log(res)
					if (res.status == 200) {} else {
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
			//评论的焦点事件
			commentsTextFocus() {
				this.inputBtn = '';
			},
			commentsTextBlur() {
				if (this.commentsText == '') this.inputBtn = 'none';

			},
			delOff() {
				this.$u.post('UserDynamic/delDynamic', {
					id: this.dynamicInfo.id
				}).then(res => {
					if (res.status == 200) {
						uni.navigateBack({
							delta: 1
						});
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
			//发布评论
			sendComments() {
				console.log(123)
				this.$u.post('UserDynamic/sendComments', {
					dynamicId: this.dynamicId,
					commentsText: this.commentsText
				}).then(res => {
					//清空输入框
					this.commentsText = '';
					if (res.status == 200) {
						//更新数据
						this.getinfo();
						this.$refs.uToast.show({
							title: '发布成功',
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
	/* 想法图片排列样式 */
	.main {

		//导航
		.nvabar {

			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;

				.nvabar-main-text {
					flex: 1;
					padding: 12rpx;
					margin-right: 56rpx;
					color: $uni-text-color;
					text-align: center;
				}
			}
		}

		.uni-list-chat__content-extra-text {
			color: #007AFF;
		}

		.dynamic {
			width: 100%;
		}

		.allImage {
			display: flex;
			margin-top: 10rpx;
			flex-wrap: wrap;
			justify-content: flex-start;
		}

		.imgList {
			margin: 0 3%;
		}

		.images:not(:nth-child(3n)) {
			/* margin-right: 10rpx; */
		}

		.text {
			margin: 1% 3% 2%;
		}

		.images {
			margin-right: 10rpx;
			display: inline-block;
		}

		.operate {
			width: 94%;
			padding: 3%;
			font-size: 14px;
		}

		.chat-custom-right {
			flex: 1;
			/* #ifndef APP-NVUE */
			display: flex;
			/* #endif */
			flex-direction: column;
			justify-content: space-between;
			align-items: flex-end;
		}

		.chat-custom-text {
			font-size: 12px;
			color: #999;
		}

		.bottom-line {
			border-bottom: 10px solid #efefef;
		}


		.user__container {
			display: flex;
			-webkit-box-orient: horizontal;
			-webkit-box-direction: normal;
			/* -webkit-flex-direction: row; */
			flex-direction: row;
			-webkit-box-flex: 1;
			/* -webkit-flex: 1; */
			flex: 1;
			padding: 10px 15px;
			position: relative;
			overflow: hidden;
		}

		.user__header {
			display: flex;
			width: 45px;
			height: 45px;
			-webkit-border-radius: 5px;
			border-color: #eee;
			border-width: 1px;
			border-style: solid;
			overflow: hidden;
			border-radius: 50px;
		}

		.user__header-image {
			display: flex;
			align-content: center;
			-webkit-box-orient: horizontal;
			-webkit-box-direction: normal;
			flex-direction: row;
			-webkit-box-pack: center;
			justify-content: center;
			-webkit-box-align: center;
			align-items: center;
			flex-wrap: wrap-reverse;
			width: 45px;
			height: 45px;
			border-radius: 5px;
			border-color: #eee;
			border-width: 1px;
			border-style: solid;
			overflow: hidden;
		}

		.user__content {
			display: flex;
			-webkit-box-orient: horizontal;
			-webkit-box-direction: normal;
			flex-direction: row;
			-webkit-box-flex: 1;
			flex: 1;
			overflow: hidden;
			padding: 2px 0;
		}

		.user__content-main {
			display: -webkit-box;
			display: -webkit-flex;
			display: flex;
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-webkit-flex-direction: column;
			flex-direction: column;
			-webkit-box-pack: justify;
			-webkit-justify-content: space-between;
			justify-content: space-between;
			padding-left: 10px;
			-webkit-box-flex: 1;
			-webkit-flex: 1;
			flex: 1;
			overflow: hidden;
		}

		.user__content-note {
			margin-top: 3px;
			color: #999;
			font-size: 12px;
			font-weight: normal;
			overflow: hidden;
		}

		.user__focus-on {
			padding: 3px 10px;
			border: 1px solid #fb5f5f;
			color: #fb5f5f;
			display: flex;
			font-size: 14px;
			border-radius: 3px;
		}

		.user__focus-off {
			padding: 3px;
			color: gray;
			font-size: 14px;
		}

		.comments-list {
			margin-bottom: 100rpx;

			.comments-list-item {
				.message_list {
					height: 110rpx;
					width: 100%;
					padding-left: $uni-spacing-row-sm;
					padding-right: $uni-spacing-row-sm;
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
						.time {
							color: $uni-text-color-grey;
							font-size: 22rpx;
						}
					}
				}
			}
		}

		.comments {
			display: flex;
			padding: 0 $uni-spacing-row-sm 0;
			position: fixed;
			bottom: 0;
			width: 100%;
			background-color: #F3F3F3;
			padding-bottom: 18rpx;

			.comments-btn {
				margin-left: 18rpx;
				width: 18%;
			}
		}
	}
</style>
