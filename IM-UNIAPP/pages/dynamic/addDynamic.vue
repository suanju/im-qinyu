<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<u-navbar class="nvabar" :is-back="true" :is-fixed="true">
			<view class="nvabar-main">
				<!-- 导航内容体 -->
				<view class="nvabar-main-text">
					<text>发布动态</text>
				</view>
			</view>
		</u-navbar>
		<view class="input">
			<view class="input-text"></view>
			<u-input v-model="data.text" type="textarea" :border="false" placeholder="发布点新鲜事吧!" height="180" />
			<u-upload ref="uUpload" :action="action" :form-data="data" @on-success="uplodSucces"></u-upload>
			<u-button class="imput-btn" @click="submit">发布</u-button>
		</view>
	</view>
</template>

<script>
	import {
		requestUrl
	} from '../../common/config.js';
	export default {
		data() {
			return {
				data: {
					fileList: [],
					text: '',
				},
				action: requestUrl + '/UserDynamic/uploadPic',

			}
		},
		mounted() {

		},
		methods: {
			submit() {
				this.$u.post('UserDynamic/addDynamic', {
					content: this.data.text,
					img: this.data.fileList
				}).then(res => {
					console.log(res)
					if (res.status == 200) {
						this.$refs.uToast.show({
							title: '发布成功',
							type: 'success',
							position: 'bottom',
							callback: () => {
							 uni.redirectTo({
							     url: './index'
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
			},
			uplodSucces(data, index, lists, name) {
				this.data.fileList = [...this.data.fileList, data.data]
				console.log(this.data.fileList)
			}
		}
	}
</script>

<style lang="scss" scoped>
	.main {
		padding: 0 $uni-spacing-row-sm 0;
		background-color: $uni-bg-color;
		height: 100vh;

		.nvabar {
			.nvabar-main {
				display: flex;
				width: 100%;
				padding: 0 $uni-spacing-row-sm 0;

				.nvabar-main-text {
					flex: 1;
					padding: 12rpx;
					margin-right: 70rpx;
					color: $uni-text-color;
					text-align: center;
				}

			}
		}

		.input {
			margin-top: 16rpx;
			border-radius: 18rpx;
			background-color: #FFFFFF;
			min-height: 570rpx;
			height: auto;

			.imput-btn {
				margin: 38rpx 18rpx 18rpx 18rpx;
			}

		}

	}
</style>
