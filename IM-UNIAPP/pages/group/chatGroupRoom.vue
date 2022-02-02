<template>
	<view class="main">
		<view>
			<u-toast ref="uToast" />
		</view>
		<!-- 消息收藏提示 -->
		<u-action-sheet :list="[{text: '收藏该消息',color: '#007aff',fontSize: 24}]" @click="setCollect()"
			v-model="collectShow"></u-action-sheet>
		<!-- 发送收藏 -->
		<u-popup v-model="starShow" mode="bottom" border-radius="34" length="70%" :closeable="true">
					<MySatr @sendCollect="sendCollect"></MySatr>
		</u-popup>
		<!-- 发送位置 -->
		<mi-map v-if="mapShow" ref="miMap" tipText="mi-Map" @updateAddress="updateAddress">
		</mi-map>

		<u-navbar class="nvabar" :is-fixed="true">
			<view class="nvabar-main">
				<!-- 导航内容体 -->
				<view class="nvabar-main-text">
					<text>{{fname}}</text>
				</view>
				<view class="nvabar-main-icon">
					<u-icon class="icon-camera" name="list" size="36" @click="getUserCard"></u-icon>
				</view>
			</view>
		</u-navbar>
		<view class="tips color_fff size_12 align_c" :class="{ 'show':ajax.loading }" @tap="getHistoryMsg">
			{{ajax.loadText}}
		</view>
		<view class="box-1" :class="{'listBoxPadding':listBoxPadding}" id="list-box" @click="inputFocus">
			<view class="talk-list">
				<view v-for="(item,index) in talkList" :key="index" :id="`msg-${item.id}`">
					<view class="item flex_col" :class=" item.uid != $store.state.userInfo.id ? 'pull':'push' "
						@touchstart="getTouchStartItem(item.id)" @touchend="getTouchendItem()">
						<u-avatar :src="staticUrl +item.portrait"></u-avatar>
						<!-- 普通文本 -->
						<view v-if="item.messageType == 'text'" class="content">{{item.message}}</view>
						<!-- 图片 -->
						<view v-else-if="item.messageType == 'pic'" class="contentPic">
							<img class="contentPic-img" @click="previewImg(item.message)" :src="item.message"
								mode="aspectFit"></img>
						</view>
						<!-- 语音 -->
						<view v-else-if="item.messageType == 'mic'" class="contentMic content"
							:style="{width:contentMicWidth(item.sec)}" @click="playMic(item.message)">
							<u-icon class="content-icon" name="play-circle" size="36"></u-icon>
							<text v-if="item.min">{{item.min}}′</text><text>{{item.sec}} ″</text>
						</view>
						<view v-if="item.messageType == 'map'" class="contentMap" @click="jumpMap(item.latitude,item.longitude,item.message)">
							<view class="map-body">
								<img class="map" :src="txStaicMap(item.latitude,item.longitude)"></img>
								<view class="address">
									<text v-if="typeof(item.message) == 'string'">位置 : {{item.message}}</text>
									<text v-else>位置 : 太偏僻了定位失灵啦</text>
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="box-2">
			<view class="flex_col">
				<view class="flex_grow">
					<input type="text" class="content" v-model="content" placeholder="请输入聊天内容"
						placeholder-style="color:#DDD;" @focus="inputFocus" :cursor-spacing="6">
				</view>
				<button class="send" @tap="send(content,'text')">发送</button>
			</view>
			<view class="icon-box">
				<u-icon name="mic" @click="getMic()" size="38"></u-icon>
				<u-icon class="icon-box-index" name="photo-fill" size="38" @click="getPhopoBlob()"></u-icon>
				<u-icon class="icon-box-index" name="camera-fill" size="38" @click="getCamera()"></u-icon>
				<u-icon class="icon-box-index" name="star-fill" size="38" @click="getStar()"></u-icon>
				<u-icon class="icon-box-index" name="map-fill" size="38" @click="getMap()"></u-icon>
			</view>
			<!-- <mic class="fun-box" v-if="micShow"></mic> -->
			<sound-recording v-if="micShow" class="fun-box" :maximum="60" :duration="150" theme="#12B8F6"
				@confirm="sendMic">></sound-recording>
		</view>
	</view>
</template>

<script>
	import {
		objectURLToBlob,
		blobToDataURL,
		fileToBase64,
		getImgToBase64
	} from '../../common/fun.js';
	import Ctpic from '../../common/uni-app-customImg.js';
	//引入底部模块
	import MySatr from '../../components/chatRoom/myStar.vue';
	import soundRecording from '@/components/sound-recording/sound-recording.vue';
	//地图相关配置
	import {txStaicMap} from '../../common/config.js';
	//用户信息组件
	import UserCard from '../../components/chatRoom/userCard.vue';
	export default {
		data() {
			return {
				talkList: [],
				fid: '',
				fname: '',
				fportrait : '',
				// lock: true, //是否开启断线重连
				ajax: {
					rows: 15, //每页数量
					page: 1, //页码
					flag: true, // 请求开关
					loading: true, // 加载中
					loadText: '正在获取消息'
				},
				content: '',
				chatSocket: {}, //聊天的ws实例
				collectID: '', //收藏消息的id
				collectShow: false, //收藏提示框
				//控制底部功能区是否显示
				micShow: false,
				listBoxPadding: false, //点击语音按钮后底部padding

				//长按事件定时器id 
				setTimeoutId: '',
				starShow: false, //发送收藏弹出层
				mapShow: false, //发送地图
				userCardShow : false,//用户信息
				positionObj: {}, //位置信息
			}
		},
		components: {
			soundRecording,
			MySatr,
			UserCard
		},
		onLoad: function(option) {
			//option为object类型，会序列化上个页面传递的参数
			this.fid = option.fid;
			this.fname = option.fname;
			this.fportrait = option.fportrait;

		},
		mounted() {
			this.link();
			this.$nextTick(() => {
				this.getHistoryMsg();
			});
		},
		onPageScroll(e) {
			if (e.scrollTop < 5) {
				this.getHistoryMsg();
			}
		},
		methods: {
			// 获取历史消息
			link() {
				this.chatSocket = uni.connectSocket({
					url: this.wsUrl + '?type=chatGroup_uid_' + this.fid + '&token=' + uni.getStorageSync('token'),
					complete: () => {}
				});
				this.chatSocket.onOpen(function(res) {
					console.log('WebSocket连接已打开！');
				});
				this.chatSocket.onError(function(res) {
					console.log('WebSocket连接打开失败，请检查！');
				});
				this.chatSocket.onMessage((res) => {
					let data = JSON.parse(res.data);
					console.log(data)
					//不为好友的情况
					if(data.status == 100){
						this.$refs.uToast.show({
							title: data.message,
							type: 'error',
							position: 'bottom',
						})
					}else{

					if (data.data.messageType == 'pic') {
						let message = JSON.parse(data.data.message)
						var pushInfo = {
							'id': data.data.id,
							'created_at': this.$u.timeFormat(data.data.tmie, 'yyyy-mm-dd-ss'),
							'message': this.staticUrl + message.path,
							'messageType': data.data.messageType,
							'width': message.imgInfo[0],
							'height': message.imgInfo[1],
							'uid': this.fid,
							'portrait': data.data.portrait,
							'type': data.data.type
						}
					} else if (data.data.messageType == 'mic') {
						let message = JSON.parse(data.data.message)
						var pushInfo = {
							'id': data.data.id,
							'created_at': this.$u.timeFormat(data.data.tmie, 'yyyy-mm-dd-ss'),
							'message': this.staticUrl + message.base64,
							'min': message.time.min,
							'sec': message.time.sec,
							'messageType': data.data.messageType,
							'uid': this.fid,
							'portrait': data.data.portrait,
							'type': data.data.type
						}
					} else if (data.data.messageType == 'map') {
						let message = JSON.parse(data.data.message)
						var pushInfo = {
							'id': data.data.id,
							'created_at': this.$u.timeFormat(data.data.tmie, 'yyyy-mm-dd-ss'),
							'message': message.address,
							'latitude': message.latitude,
							'longitude': message.longitude,
							'messageType': data.data.messageType,
							'uid': this.fid,
							'portrait': data.data.portrait,
							'type': data.data.type
						}
					} else {
						var pushInfo = {
							'id': data.data.id,
							'created_at': this.$u.timeFormat(data.data.tmie, 'yyyy-mm-dd-ss'),
							'message': data.data.message,
							'messageType': data.data.messageType,
							'uid': this.fid,
							'portrait': data.data.portrait,
							'type': data.data.type
						}
					}
					}
					//添加到消息列表
					console.log(pushInfo)
					this.talkList.push(pushInfo);
					//延迟保证能够到底
					setTimeout(() => {
						uni.pageScrollTo({
							scrollTop: 999999,
							duration: 0
						})
					}, 300)
				});

			},
			//输入框获得焦点触发事件
			inputFocus() {
				this.micShow = false;
				this.listBoxPadding = false;
			},

			getHistoryMsg() {
				if (!this.ajax.flag) {
					return; //
				}

				// 此处用到 ES7 的 async/await 知识，为使代码更加优美。不懂的请自行学习。
				let get = async () => {
					this.hideLoadTips();
					this.ajax.flag = false;
					let data = await this.joinHistoryMsg();

					console.log('----- 模拟数据格式，供参考 -----');
					console.log(data); // 查看请求 返回的数据结构 

					// 获取待滚动元素选择器，解决插入数据后，滚动条定位时使用
					let selector = '';
					if (this.ajax.page > 1) {
						// 非第一页，则取历史消息数据的第一条信息元素
						selector = `#msg-${this.talkList[0].id}`;
					} else {
						if (data.length != 0) {
							//不为空怕报错
							// 第一页，则取当前消息数据的最后一条信息元素
							selector = `#msg-${data[data.length-1].id}`;
						} else {
							//空值不设置定位
							return;
						}

					}
					// 将获取到的消息数据合并到消息数组中
					this.talkList = [...data, ...this.talkList];

					// 数据挂载后执行，不懂的请自行阅读 Vue.js 文档对 Vue.nextTick 函数说明。
					this.$nextTick(() => {
						// 设置当前滚动的位置
						console.log(selector)
						this.setPageScrollTo(selector);

						this.hideLoadTips(true);

						if (data.length < this.ajax.rows) {
							// 当前消息数据条数小于请求要求条数时，则无更多消息，不再允许请求。
							// 可在此处编写无更多消息数据时的逻辑
						} else {
							this.ajax.page++;
							// 延迟 200ms ，以保证设置窗口滚动已完成
							setTimeout(() => {
								this.ajax.flag = true;
							}, 200)
						}

					})
				}
				get();
			},
			// 拼接历史记录消息
			joinHistoryMsg() {
				// 此处用到 ES6 的 Promise 知识，不懂的请自行学习。
				return new Promise((done, fail) => {
					// 无数据请求接口，由 setTimeout 模拟，正式项目替换为 ajax 即可。
					this.$u.post('Chat/recordGroup', {
						fid: this.fid,
						page: this.ajax.page,
						rows: this.ajax.rows
					}).then(res => {
						if (res.status == 200) {
							let data = res.data.filter(e => {
								if (e.messageType == 'pic') {
									let data = JSON.parse(e.message);
									e.message = this.staticUrl + data.path;
									e.width = data.imgInfo[0];
									e.height = data.imgInfo[1];
								}
								if (e.messageType == 'mic') {
									let data = JSON.parse(e.message);
									e.message = this.staticUrl + data.base64;
									e.min = data.time.min;
									e.sec = data.time.sec;
								}
								if (e.messageType == 'map') {
									let data = JSON.parse(e.message);
									e.message = data.address;
									e.latitude = data.latitude;
									e.longitude = data.longitude;
								}
								return e;
							})
							done(data.reverse())
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
				})
			},
			// 设置页面滚动位置
			setPageScrollTo(selector) {
				let view = uni.createSelectorQuery().in(this).select(selector);
				view.boundingClientRect((res) => {
					uni.pageScrollTo({
						scrollTop: res.top - 30, // -30 为多显示出大半个消息的高度，示意上面还有信息。
						duration: 0
					});
				}).exec();
			},
			// 隐藏加载提示
			hideLoadTips(flag) {
				if (flag) {
					this.ajax.loadText = '消息获取成功';
					setTimeout(() => {
						this.ajax.loading = false;
					}, 300);
				} else {
					this.ajax.loading = true;
					this.ajax.loadText = '正在获取消息';
				}
			},
			// 发送信息
			send(message, messageType, path) {
				let _this = this
				if (!this.content && messageType == 'text') {
					this.$refs.uToast.show({
						title: '请输入内容',
						type: 'error',
						position: 'bottom',
					})
					return;
				}
				let msg;
				msg = {
					'type': 'chatGroup',
					'message': message,
					'uid': this.fid,
					'messageType': messageType
				}
				let data;
				switch (messageType) {
					case 'pic':
						data = {
							'created_at': this.$u.timeFormat(Date.parse(new Date()), 'yyyy-mm-dd-ss'),
							'message': message,
							'uid': this.$store.state.userInfo.id,
							'portrait': this.$store.state.userInfo.portrait,
							'messageType': messageType
						}
						break;
					case 'mic':
						let src = JSON.parse(message);
						console.log(message)
						data = {
							'created_at': this.$u.timeFormat(Date.parse(new Date()), 'yyyy-mm-dd-ss'),
							'message': path,
							'uid': this.$store.state.userInfo.id,
							'min': src.time.min,
							'sec': src.time.sec,
							'portrait': this.$store.state.userInfo.portrait,
							'messageType': messageType
						}
						break;
					case 'collect':
						message = message[0];
						//发送的是收藏的话需要再次判断收藏类型
						switch (message.collect_type) {
							case 'pic':
								msg = {
									'type': 'chatGroup',
									'message': message.id,
									'uid': this.fid,
									'messageType': 'collect'
								}
								data = {
									'created_at': this.$u.timeFormat(Date.parse(new Date()), 'yyyy-mm-dd-ss'),
									'message': message.collect,
									'uid': this.$store.state.userInfo.id,
									'portrait': this.$store.state.userInfo.portrait,
									'messageType': message.collect_type
								}
								break;
							case 'mic':
								msg = {
									'type': 'chatGroup',
									'message': message.id,
									'uid': this.fid,
									'messageType': 'collect'
								}
								data = {
									'created_at': this.$u.timeFormat(Date.parse(new Date()), 'yyyy-mm-dd-ss'),
									'message': message.collect,
									'uid': this.$store.state.userInfo.id,
									'min': message.min,
									'sec': message.sec,
									'portrait': this.$store.state.userInfo.portrait,
									'messageType': message.collect_type
								}
								break;
							default:
								msg = {
									'type': 'chatGroup',
									'message': message.id,
									'uid': this.fid,
									'messageType': 'collect'
								}
								data = {
									'created_at': this.$u.timeFormat(Date.parse(new Date()), 'yyyy-mm-dd-ss'),
									'message': message.collect,
									'uid': this.$store.state.userInfo.id,
									'portrait': this.$store.state.userInfo.portrait,
									'messageType': message.collect_type
								}
						}
						//收藏判断结束
						break;
						//发送消息为地图类型
					case 'map':
						let address = JSON.parse(message);
						console.log(message)
						data = {
							'created_at': this.$u.timeFormat(Date.parse(new Date()), 'yyyy-mm-dd-ss'),
							'message': address.address,
							'uid': this.$store.state.userInfo.id,
							'latitude': address.latitude,
							'longitude': address.longitude,
							'portrait': this.$store.state.userInfo.portrait,
							'messageType': messageType
						}
						break;
					default:
						data = {
							'created_at': this.$u.timeFormat(Date.parse(new Date()), 'yyyy-mm-dd-ss'),
							'message': message,
							'uid': this.$store.state.userInfo.id,
							'portrait': this.$store.state.userInfo.portrait,
							'messageType': messageType
						}
				}

				//定义加载本地信息
				console.log(msg);

				uni.sendSocketMessage({
					data: JSON.stringify(msg),
					success: function() {
						_this.$nextTick(() => {
							_this.talkList.push(data);
							// 清空内容框中的内容
							_this.content = '';
							setTimeout(() => {
								uni.pageScrollTo({
									scrollTop: 999999, // 设置一个超大值，以保证滚动条滚动到底部
									duration: 0
								})
							}, 100);
						})
					},
					fail: function(e) {
						console.log(e)
						_this.$refs.uToast.show({
							title: '发送失败',
							type: 'error',
							position: 'bottom',
						})
					}
				});
			},
			// 预览图片
			previewImg(e) {
				// 获取聊天中的图片
				let imgList = [];
				let imgIndex;
				for (let i = 0; i < this.talkList.length; i++) {
					if (this.talkList[i].messageType == 'pic') {
						if (this.talkList[i].message == e) {
							imgIndex = imgList.length;
						}
						let img = this.talkList[i].message
						imgList = [...imgList, img]
					}
				}
				console.log(imgIndex)
				uni.previewImage({
					current: imgIndex,
					urls: imgList,
					longPressActions: {
						itemList: ['保存图片'],
					}
				})
			},
			getPhopoBlob() {
				uni.chooseImage({
					count: 1, //默认9
					sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
					sourceType: ['album'], //从相册选择
					success: (res) => {
						//不同平台不同方法
						// #ifdef H5
						let phopo = res.tempFiles[0];
						fileToBase64(phopo, (e) => {
							this.send(e, 'pic')
						})
						// #endif

						// #ifdef APP-PLUS
						let phopo = res.tempFilePaths[0];
						const ctpic = new Ctpic();
						let img = ctpic.app_appendFile({
							path: phopo,
							isNet: false,
							format: 'png'
						})
						console.log(img);
						img.then(
							(data) => {
								this.send(data, 'pic')
							}
						).catch(
							(e) => {
								console.log(e)
							}
						)
						// #endif
					}
				});
			},
			getCamera() {
				uni.chooseImage({
					count: 1, //默认9
					sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
					sourceType: ['camera'], //从相机拍照选择
					success: (res) => {
						//不同平台不同方法
						// #ifdef H5
						let phopo = res.tempFiles[0];
						fileToBase64(phopo, (e) => {
							this.send(e, 'pic')
						})
						// #endif

						// #ifdef APP-PLUS
						let phopo = res.tempFilePaths[0];
						const ctpic = new Ctpic();
						let img = ctpic.app_appendFile({
							path: phopo,
							isNet: false,
							format: 'png'
						})
						console.log(img);
						img.then(
							(data) => {
								this.send(data, 'pic')
							}
						).catch(
							(e) => {
								console.log(e)
							}
						)
						// #endif
					}
				});
			},
			//发送语音消息
			getMic() {
				//不同平台不同方法
				// #ifdef H5
				// this.$refs.uToast.show({
				// 	title: 'H5平台不能发送语音哟!',
				// 	type: 'error',
				// 	position: 'bottom',
				// })
				this.micShow = true;
				this.listBoxPadding = true;
				setTimeout(() => {
					uni.pageScrollTo({
						scrollTop: 999999, // 设置一个超大值，以保证滚动条滚动到底部
						duration: 0
					})
				}, 300);
				
				// #endif

				// #ifdef APP-PLUS
				plus.android.requestPermissions(['android.permission.RECORD_AUDIO'], (e) => {
					if (e.deniedAlways.length > 0) { //权限被永久拒绝
						// 弹出提示框解释为何需要定位权限，引导用户打开设置页面开启
						this.$refs.uToast.show({
							title: '录音权限被拒绝',
							type: 'warning',
							position: 'bottom',
						})
					}
					if (e.deniedPresent.length > 0) { //权限被临时拒绝
						plus.android.requestPermissions(['android.permission.RECORD_AUDIO'])
					}
					if (e.granted.length > 0) { //权限被允许
						//调用依赖获取定位权限的代码
						this.micShow = true;
						this.listBoxPadding = true;
						setTimeout(() => {
							uni.pageScrollTo({
								scrollTop: 999999, // 设置一个超大值，以保证滚动条滚动到底部
								duration: 0
							})
						}, 300);
					}
				}, function(e) {
					console.log('Request Permissions error:' + JSON.stringify(e));
				});
				// #endif
			},
			sendMic(base64, time, path) {
				let text = {
					base64,
					time
				}
				console.log(JSON.stringify(text))
				this.send(JSON.stringify(text), 'mic', path)
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
			contentMicWidth(e) {
				return 120 + e * 5 + 'rpx';
			},
			//长按消息
			getTouchStartItem(id) {
				this.setTimeoutId = setTimeout(() => {
					this.collectID = id;
					this.collectShow = true;
				}, 1500)
			},
			getTouchendItem() {
				clearTimeout(this.setTimeoutId)
			},
			//点击了收藏
			setCollect() {
				console.log('收藏ID', this.collectID);
				this.$u.post('Collect/setCollectGroup', {
					messageId: this.collectID
				}).then(res => {
					if (res.status == 200) {
						this.$refs.uToast.show({
							title: '收藏成功',
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
			},
			//点击收藏
			getStar() {
				this.starShow = true;
			},
			//发送收藏的消息
			sendCollect(message, messageType) {
				console.log(message, messageType)
				this.send(message, messageType)
				this.starShow = false;
			},
			//调用地图
			getMap() {
				this.mapShow = true
			},
			// 更新地址并关闭地图
			updateAddress(addressObj) {
				this.mapShow = false;
				//判断是否选择好了地址
				if (addressObj.latitude == "" && addressObj.longitude == "") {
					this.$refs.uToast.show({
						title: '地址出现错误',
						type: 'top',
						position: 'bottom',
					})
					return false;
				}
				this.positionObj = addressObj
				this.send(JSON.stringify(this.positionObj), 'map');
				console.log(this.positionObj)
			},
			txStaicMap(x,y){
				let src =  txStaicMap + 'center=' + x + ',' + y + '&markers=color:red|'+ x + ',' + y
				return src;
			},
			//点击用户资料
			getUserCard(){
				uni.navigateTo({
					url: './groupInfo?id=' + this.fid
				});
			},
			//点击地图消息
			jumpMap(latitude,longitude,title){
				uni.navigateTo({
					url: './map?latitude=' + latitude + '&longitude=' + longitude  + '&title=' + title 
				});
			}

		},

		beforeDestroy() {
			//销毁ws
			this.chatSocket.close();
			this.chatSocket.onClose(function(res) {
				console.log('聊天页面WebSocket已关闭！');
			});
		}
	}
</script>

<style lang="scss" scoped>
	@import "../../static/css/chat/css/global.scss";

	.main {
		background-color: #F3F3F3;
		

		/* 顶部导航栏 */
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

		page {
			background-color: #F3F3F3;
			font-size: 28rpx;
			height: 100%;
		}


		/* 加载数据提示 */
		.tips {
			position: fixed;
			left: 0;
			top: var(--window-top);
			width: 100%;
			z-index: 9;
			background-color: rgba(0, 0, 0, 0.15);
			height: 72rpx;
			line-height: 72rpx;
			transform: translateY(-80rpx);
			transition: transform 0.3s ease-in-out 0s;

			&.show {
				transform: translateY(0);
			}
		}

		.box-1 {
			width: 100%;
			height: auto;
			min-height: 100%;
			padding-bottom: 142rpx;
			box-sizing: content-box;

			/* 兼容iPhoneX */
			margin-bottom: 100rpx;
			margin-bottom: constant(safe-area-inset-bottom);
			margin-bottom: env(safe-area-inset-bottom);
		}

		.listBoxPadding {
			padding-bottom: 642rpx;
		}

		.box-2 {
			position: fixed;
			left: 0;
			width: 100%;
			bottom: 0;
			height: auto;
			z-index: 2;
			// border-top: #e5e5e5 solid 1px;
			box-sizing: content-box;
			background-color: #F3F3F3;

			/* 兼容iPhoneX */
			padding-bottom: 0;
			padding-bottom: constant(safe-area-inset-bottom);
			padding-bottom: env(safe-area-inset-bottom);

			>view {
				padding: 0 20rpx;
				height: 100rpx;
			}

			.content {
				background-color: #fff;
				height: 64rpx;
				padding: 0 20rpx;
				border-radius: 32rpx;
				font-size: 28rpx;
			}

			.send {
				background-color: $uni-text-color;
				color: #fff;
				height: 64rpx;
				margin-left: 20rpx;
				border-radius: 32rpx;
				padding: 0;
				width: 120rpx;
				line-height: 62rpx;

				&:active {
					background-color: #5fc496;
				}
			}

			.icon-box {
				height: 100%;

				.icon-box-index {
					margin-left: 18%;

				}
			}

			.fun-box {
				height: 500rpx;
			}
		}

		.talk-list {
			padding-bottom: 20rpx;

			/* 消息项，基础类 */
			.item {

				padding: 20rpx 20rpx 0 20rpx;
				align-items: flex-start;
				align-content: flex-start;
				color: #333;

				.pic {
					width: 92rpx;
					height: 92rpx;
					border-radius: 50%;
					border: #fff solid 1px;
				}

				.content {
					padding: 20rpx;
					border-radius: 4px;
					max-width: 500rpx;
					word-break: break-all;
					line-height: 52rpx;
					position: relative;
				}

				/* 收到的消息 */
				&.pull {
					.content {
						margin-left: 32rpx;
						background-color: $uni-bg-color-chat;
						display: flex;

						.content-icon {
							flex: 1;
						}

						&::after {
							content: '';
							display: block;
							width: 0;
							height: 0;
							border-top: 16rpx solid transparent;
							border-bottom: 16rpx solid transparent;
							border-right: 20rpx solid $uni-bg-color-chat;
							position: absolute;
							top: 30rpx;
							left: -18rpx;
						}
					}

					.contentPic {
						margin-left: 32rpx;

						.contentPic-img {
							border-radius: 24rpx;
							float: left;
							max-width: 70%;
							max-height: 400rpx;
						}
					}

					.contentMic {
						min-width: 120rpx;
						max-width: 420rpx;
					}

					.contentMap {
						z-index: 1;
						width: 100%;
						margin-left: 32rpx;
						padding: 14rpx;

						.map-body {
							margin: 16rpx;
							float: left;
							width: 80%;
							box-shadow: 0 4rpx 8rpx 0 rgba(#007aff, 0.2), 0 6rpx 20rpx 0 rgba(#007aff, 0.19);

							.map {
								width: 100%;
							}

							.address {
								overflow:hidden;//超出一行文字自动隐藏 
								text-overflow:ellipsis;//文字隐藏后添加省略号
								white-space:nowrap;//强制不换行
								text-align: center;
								height: 100%;
								
								.address-btn{
									
								}
							}
						}
					}


				}

				/* 发出的消息 */
				&.push {
					/* 主轴为水平方向，起点在右端。使不修改DOM结构，也能改变元素排列顺序 */
					flex-direction: row-reverse;

					.content {
						margin-right: 32rpx;
						background-color: #fff;
						display: flex;

						.content-icon {
							flex: 1;
						}

						&::after {
							content: '';
							display: block;
							width: 0;
							height: 0;
							border-top: 16rpx solid transparent;
							border-bottom: 16rpx solid transparent;
							border-left: 20rpx solid #fff;
							position: absolute;
							top: 30rpx;
							right: -18rpx;
						}
					}

					.contentPic {
						margin-right: 32rpx;

						.contentPic-img {
							border-radius: 24rpx;
							max-width: 70%;
							max-height: 400rpx;
							float: right;


						}
					}

					.contentMic {
						min-width: 120rpx;
						max-width: 420rpx;
					}

					.contentMap {
						z-index: 1;
						width: 100%;
						margin-right: 32rpx;
						padding: 14rpx;

						.map-body {
							margin: 16rpx;
							float: right;
							width: 80%;
							box-shadow: 0 4rpx 8rpx 0 rgba(#007aff, 0.2), 0 6rpx 20rpx 0 rgba(#007aff, 0.19);

							.map {
								width: 100%;
							}

							.address {
								overflow:hidden;//超出一行文字自动隐藏 
								text-overflow:ellipsis;//文字隐藏后添加省略号
								white-space:nowrap;//强制不换行
								text-align: center;
								height: 100%;
								
								.address-btn{
									
								}
							}
						}
					}

				}
			}
		}



	}
</style>
