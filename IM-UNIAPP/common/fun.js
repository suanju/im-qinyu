function objectURLToBlob(url, callback) {
	var http = new XMLHttpRequest();
	http.open("GET", url, true);
	http.responseType = "blob";
	http.onload = function(e) {
		if (this.status == 200 || this.status === 0) {
			callback(this.response)
		}
	}
	http.send()
}

function blobToDataURL(blob, callback) {
	let a = new FileReader();
	a.onload = function(e) {
		callback(e.target.result);
	}
	a.readAsDataURL(blob);
}

function fileToBase64(file, callback) {
	const fileReader = new FileReader()
	fileReader.readAsDataURL(file)
	fileReader.onload = function() {
		callback(this.result)
	}
}

function getImgToBase64(url, callback) { //将图片转换为Base64
	var canvas = document.createElement('canvas'),
		ctx = canvas.getContext('2d'),
		img = new Image;
	img.crossOrigin = 'Anonymous';
	img.onload = function() {
		canvas.height = img.height;
		canvas.width = img.width;
		ctx.drawImage(img, 0, 0);
		var dataURL = canvas.toDataURL('image/png');
		console.log(dataURL)
		callback(dataURL);
		canvas = null;
	};
	img.src = url;
}

function deepCopy(p, c) {
　　　　var c = c || {};
　　　　for (var i in p) {
　　　　　　if (typeof p[i] === 'object') {
　　　　　　　　c[i] = (p[i].constructor === Array) ? [] : {};
　　　　　　　　deepCopy(p[i], c[i]);
　　　　　　} else {
　　　　　　　　　c[i] = p[i];
　　　　　　}
　　　　}
　　　　return c;
　　}
export {
	objectURLToBlob,
	blobToDataURL,
	fileToBase64,
	getImgToBase64,
	deepCopy
}
