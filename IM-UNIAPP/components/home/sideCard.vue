<template>
	<view class="main">
		<view class="head" @click="jump('/pages/personal/index')">
			<view>
				<u-avatar class="nvabar-main-avatar" size="180" :src="staticUrl +$store.state.userInfo.portrait">
				</u-avatar>
			</view>
			<view class="head-text">
				<view class="head-text-name">{{$store.state.userInfo.username}}</view>
				<view class="head-text-sgin"><text>签名:</text>你好世界!</view>
			</view>
		</view>
		<view class="list">
			<view class="options" @click="jump('/pages/dynamic/myDynamic')">
				<u-icon name="qzone-circle-fill" class="options-before-icon" size="32"></u-icon><text
					class="options-text">我的动态</text>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view>
		<!-- 	<view class="options">
				<u-icon name="rmb-circle-fill" class="options-before-icon" size="32"></u-icon><text
					class="options-text">我的钱包</text>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view> -->
			<view class="options" @click="jump('/pages/collect/index')">
				<u-icon name="star-fill" class="options-before-icon" size="32"></u-icon><text
					class="options-text">我的收藏</text>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view>
		<!-- 	<view class="options">
				<u-icon name="photo-fill"  class="options-before-icon" size="32"></u-icon><text
					class="options-text">我的相册</text>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view> -->
			<view class="options">
				<u-icon name="error-circle-fill" class="options-before-icon" size="32"></u-icon><text
					class="options-text">关于</text>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view>
			<view class="options" @click="modifyModalShow">
				<u-icon name="close-circle-fill" class="options-before-icon" size="32"></u-icon><text
					class="options-text">退出登入</text>
				<u-icon class="options-icon" name="arrow-right"></u-icon>
			</view>

		</view>
	</view>
</template>

<script>
	export default {
		methods: {
			jump(url){
				uni.navigateTo({
					url:url
				});
				
			},
			modifyModalShow(){
				this.$emit('modifyModalShow')
			}
		}
	}
</script>

<style lang="scss" scoped>
	.main {
		width: 100%;
		height: 100%;
		background-image: url(../../static/img/home/bg.png);
		background-size: contain;

		.head {
			padding: 22rpx;
			display: flex;

			.head-text {
				flex: 1;
				padding: 30rpx 18rpx;
				font-size: 36rpx;

				.head-text-sgin {
					padding-top: 14rpx;
					font-size: 22rpx;
					color: $uni-text-color-grey;
				}
			}
		}

		.list {
			.options {
				height: 84rpx;
				width: 100%;
				padding-left: $uni-spacing-row-sm;
				border-bottom: solid 1rpx $uni-bg-color;
				display: flex;
				align-items: center;

				.options-before-icon {
					margin-right: 10rpx;
				}

				.options-text {
					flex: 1;
				}

				.options-icon {
					padding-right: $uni-spacing-row-sm;
				}
			}
		}
	}
</style>
