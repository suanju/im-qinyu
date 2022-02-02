function formattingTime(timespan) {

	//判断是否为秒级别时间戳
	if (timespan < 10000000000) {
		timespan = timespan * 1000;
	}
	//旧的时间
	let dateTime = new Date(timespan) // 将传进来的字符串或者毫秒转为标准时间
	let year = dateTime.getFullYear()
	let month = dateTime.getMonth()
	let day = dateTime.getDate()
	let hour = dateTime.getHours()
	let minute = dateTime.getMinutes()
	let second = dateTime.getSeconds()
	//新时间
	let Ntime = Date.parse(new Date()); //时间戳 
	let NdateTime = new Date() // 将传进来的字符串或者毫秒转为标准时间
	let Nyear = NdateTime.getFullYear()
	let Nmonth = NdateTime.getMonth()
	let Nday = NdateTime.getDate()
	let Nhour = NdateTime.getHours()
	let Nminute = NdateTime.getMinutes()
	let Nsecond = NdateTime.getSeconds()
	//今天0点时间
	let timeToday = Math.round(
		new Date(new Date().toLocaleDateString()).getTime()
	).toString();
	//昨天0点时间
	let timeYesterday = Math.round(
		new Date(new Date().toLocaleDateString()).getTime() - 1 * 24 * 60 * 60 * 1000
	).toString();
	//前天0点时间
	let timeTwodays = Math.round(
		new Date(new Date().toLocaleDateString()).getTime() - 2 * 24 * 60 * 60 * 1000
	).toString();
	//6天0点时间
	let timeSdays = Math.round(
		new Date(new Date().toLocaleDateString()).getTime() - 6 * 24 * 60 * 60 * 1000
	).toString();
	var ftmtime = '';
	if (year == Nyear && month == Nmonth && day == Nday) {
		//同一天
		if (hour < 10) {
			if (minute < 10) {
				ftmtime = '上午' + hour + ':' + '0' + minute;
			} else {
				ftmtime = '上午' + hour + ':' + minute;
			}

		} else {
			ftmtime = hour + ':' + minute;
		}
	}

	if (timeToday > timespan && timespan > timeYesterday) {
		//昨天
		ftmtime = '昨天';
	}
	if (timeYesterday > timespan && timespan > timeSdays) {
		//6天内显示星期
		if (dateTime.getDay() == 1) {
			ftmtime = '星期一'
		};
		if (dateTime.getDay() == 2) {
			ftmtime = '星期二'
		};
		if (dateTime.getDay() == 3) {
			ftmtime = '星期三'
		};
		if (dateTime.getDay() == 4) {
			ftmtime = '星期四'
		};
		if (dateTime.getDay() == 5) {
			ftmtime = '星期五'
		};
		if (dateTime.getDay() == 6) {
			ftmtime = '星期六'
		};
		if (dateTime.getDay() == 7) {
			ftmtime = '星期天'
		};
	}
	if (timespan < timeSdays) {
		ftmtime = year + '-' + month + '-' + day
	}
	return ftmtime;
}


function timestampFormat(timestamp) {
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
}
function datetime_to_unix(datetime){
    var tmp_datetime = datetime.replace(/:/g,'-');
    tmp_datetime = tmp_datetime.replace(/ /g,'-');
    var arr = tmp_datetime.split("-");
    var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return parseInt(now.getTime()/1000);
}
 
function unix_to_datetime(unix) {
    var now = new Date(parseInt(unix) * 1000);
    return now.toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
}
export {
	formattingTime,
	timestampFormat,
	datetime_to_unix,
	unix_to_datetime

}
