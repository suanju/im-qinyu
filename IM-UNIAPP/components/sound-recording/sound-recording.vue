<template>
	<view class="recorder">
		<view class="re-top" v-if="showTop">
			<!--  <view class="re-cancel" @click="cancel">取消</view> -->
		<!-- 	<view class="re-confirm" :style="{color: theme}" @click="confirm">{{ confirmText }}</view> -->
		</view>
		<text class="title">{{ finish ? '点击播放' : '长按录制语音' }}</text>
		<view class="recorder-box" v-if="!finish" @click="handle" @longpress="onStartRecoder" @touchend="onEndRecoder">
			<u-circle-progress :active-color="theme" :duration="0" :percent="calcProgress">
				<view class="u-progress-content">
					<image src="../../static/icon/chat/chatRoom/mic.png" mode="aspectFit" :style="{
            width: width,
            height: height
          }"></image>
				</view>
			</u-circle-progress>
		</view>
		<view class="recorder-box" v-else @click="playVoice">		
          <image  @click="reset" class="recorder-box-no" src="../../static/icon/chat/chatRoom/no.png" ></image>
			<u-circle-progress :active-color="theme" :duration="0" :percent="playProgress">
				<view class="u-progress-content">
					<image src="../../static/icon/chat/chatRoom/start.png" mode="aspectFit" :style="{
            width: width,
            height: height
          }" v-if="!playStatus"></image>
					<image src="../../static/icon/chat/chatRoom/pause.png" mode="aspectFit" :style="{
            width: width,
            height: height
          }" v-else></image>
				</view>
			</u-circle-progress>
			 <image  @click.stop="confirm" class="recorder-box-yes" src="../../static/icon/chat/chatRoom/yes.png" ></image>
		</view>
		<text class="now-date">{{ reDate }}</text>
	</view>
</template>

<script>
	import uCircleProgress from '../u-circle-progress/u-circle-progress.vue'
	const recorderManager = uni.getRecorderManager();
	const innerAudioContext = uni.createInnerAudioContext();
	export default {
		components: {
			uCircleProgress
		},
		props: {
			width: {
				type: String,
				default: '60rpx'
			},
			height: {
				type: String,
				default: '60rpx'
			},
			showTop: {
				type: Boolean,
				default: true
			},
			maximum: {
				type: [Number, String],
				default: 15
			},
			duration: {
				type: Number,
				default: 20
			},
			theme: {
				type: String,
				default: '#32b99d'
			},
			confirmText: {
				type: String,
				default: '发送'
			}
		},
		data() {
			return {
				reDate: '00:00',
				sec: 0,
				min: 0,
				finish: false,
				voicePath: '',
				playProgress: 100,
				playTimer: null,
				timer: null,
				playStatus: false,
			};
		},
		created() {
			// 监听
			this.onMonitorEvents()
		},
		computed: {
			// 录制时间计算
			calcProgress() {
				return (this.sec + (this.min * 60)) / this.maximum * 100
			}
		},
		methods: {
			// 完成事件
			confirm() {
				// if (!innerAudioContext.paused) {
				// 	innerAudioContext.stop()
				// }
                this.Audio2dataURL(this.voicePath);

			},
			// 取消事件
			cancel() {
				if (!innerAudioContext.paused) {
					innerAudioContext.stop()
				}
				this.$emit('cancel')
			},
			// 点击事件
			handle() {
				this.$emit('click')
			},
			// 重新录制
			reset() {
				this.voicePath = ''
				this.min = 0
				this.sec = 0
				this.reDate = '00:00'
				this.playProgress = 100
				this.finish = false
				this.$emit('reset')
			},
			// 播放暂停录音
			playVoice() {
				innerAudioContext.src = this.voicePath;

				if (innerAudioContext.paused) {
					innerAudioContext.play()
					this.playStatus = true
				} else {
					innerAudioContext.stop();
				}
				this.$emit('playVoice', innerAudioContext.paused)
			},
			// 录制结束
			onEndRecoder() {
				recorderManager.stop()
			},
			// 开始录制
			onStartRecoder() {
				recorderManager.start({
					duration: this.maximum * 1000
				})
			},
			// 监听
			onMonitorEvents() {
				// 录制开始
				recorderManager.onStart(() => {
					uni.showLoading({
						title: '录制中...'
					})
					this.startDate()
					this.$emit('start')
				})
				// 录制结束
				recorderManager.onStop(({
					tempFilePath
				}) => {
					this.voicePath = tempFilePath
					clearInterval(this.timer)
					uni.hideLoading()
					this.finish = true
					this.$emit('end')
				})
				// 播放进度
				innerAudioContext.onTimeUpdate(() => {
					//有bug 暂时抛弃
					// let totalDate = innerAudioContext.duration
					// let nowTime = innerAudioContext.currentTime
					// let surplus = totalDate - nowTime
					// // this.playProgress = Math.ceil(surplus / totalDate * 100)

					// let _min = Math.floor(surplus / 60)
					// if (_min < 10) _min = '0' + _min;
					// let _sec = Math.floor(surplus % 60)
					// if (_sec < 10) _sec = '0' + _sec;
					// this.reDate = _min + ':' + _sec
					// console.log(this.playProgress)
				})
				// 播放暂停
				innerAudioContext.onPause(() => {
					this.resetDate()
					this.playProgress = 100
					this.playStatus = false
					console.log('播放暂停')
					this.$emit('stop')
				})
				// 播放停止
				innerAudioContext.onStop(() => {
					this.resetDate()
					// this.playProgress = 100
					this.playStatus = false
					console.log('播放停止')
					this.$emit('stop')
				})
			},
			// 录音计时
			startDate() {
				clearInterval(this.timer)
				this.sec = 0
				this.min = 0
				this.timer = setInterval(() => {
					this.sec += this.duration / 1000
					if (this.sec >= 60) {
						this.min++
						this.sec = 0
					}
					this.resetDate()
				}, this.duration)
			},
			// 播放时间
			resetDate() {
				let _s = this.sec < 10 ? '0' + parseInt(this.sec) : parseInt(this.sec)
				let _m = this.min < 10 ? '0' + this.min : this.min
				this.reDate = _m + ':' + _s
			},
			Audio2dataURL(path) {
				plus.io.resolveLocalFileSystemURL(path, (entry) =>{
					entry.file((file) =>{
						var reader = new plus.io.FileReader();
						reader.readAsDataURL(file);
						reader.onloadend = (e)  =>{
							//将结果输出到chatroom
							this.$emit('confirm',e.target.result,{sec: Math.round(this.sec),min: Math.round(this.min)},this.voicePath);
							//清空本次内容
							this.reset();
							
						};
					}, (e) => {
						mui.toast("读写出现异常: " + e.message);
					})
				})
			}
		}
	}
</script>

<style lang="scss">
	.recorder {
		position: relative;
		display: flex;
		align-items: center;
		flex-direction: column;
		background-color: #F3F3F3;
		font-size: 24rpx;
		width: 100%;

		.re-top {
			display: flex;
			justify-content: space-between;
			padding: 10rpx 20rpx;
			width: 100%;
			font-size: 28rpx;
			box-sizing: border-box;
		}

		.title {
			font-size: 36rpx;
			color: #333;
			padding: 20rpx 0 30rpx;
		}

		.recorder-box {
			position: relative;
			text-align: center;
			.recorder-box-no{
				width: 80rpx;
				height: 80rpx;
				margin-top: 40rpx;
				margin-right: 80rpx;
				float: left;
			}
			.recorder-box-yes{
				width: 80rpx;
				height: 80rpx;
				margin-top: 40rpx;
				margin-left: 80rpx;
				float: right;
			}
		}

		.now-date {
			font-size: 28rpx;
			color: #666;
			padding: 20rpx 0;
		}
	}
</style>
