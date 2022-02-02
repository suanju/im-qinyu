<template>
	<view class="main">
		<view>
		<u-navbar class="nvabar" :is-fixed="true" title="位置选择" title-color=" #007aff" title-size="26">
		</u-navbar>
		</view>

		<map :longitude="longitude" :latitude="latitude" class="map" :markers="markers"></map>
	</view>
</template>

<script>
	export default{
		data(){
			return{
				longitude:'',
				latitude:'',
			
			}
		},
		onLoad: function(option) {
			//option为object类型，会序列化上个页面传递的参数
			this.longitude = option.longitude;
			this.latitude = option.latitude;
			this.title = option.title
			
			this.markers = [{
						latitude:this.latitude,
						longitude:this.longitude,
						iconPath: '../../static/img/chat/markersMap.png',
						title:this.title,
						width:30,
						height:30,
	
					}]
		},
		methods:{
			
		}
	}
</script>

<style lang="scss" scoped>
	page{
		height: 100%;
	}
	.main{
		height: 100%;
		.map{
			width: 100%;
			height: 100%;
		}
	}
	
</style>
