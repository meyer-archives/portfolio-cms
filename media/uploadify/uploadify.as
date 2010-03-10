/*
Uploadify v2.1.0
Release Date: August 24, 2009

Copyright (c) 2009 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

import flash.external.ExternalInterface;
import flash.net.*;
import flash.events.*;
import flash.display.*;
import com.adobe.serialization.json.JSON;

// Align the stage to the top left and don't scale it
stage.align = StageAlign.TOP_LEFT;
stage.scaleMode = StageScaleMode.NO_SCALE;

// Create all the variables
var param:Object = LoaderInfo(this.root.loaderInfo).parameters;
var fileRefSingle:FileReference    = new FileReference();
var fileRefMulti:FileReferenceList = new FileReferenceList();
var fileRefListener:Object = new Object();
var fileQueue:Array        = new Array();
var fileItem:Object        = new Object();
var activeUploads:Object   = new Object();
var errorArray:Array       = new Array();
var counter:Number        = 0;
var filesSelected:Number  = 0;
var filesReplaced:Number  = 0;
var filesUploaded:Number  = 0;
var filesChecked:Number   = 0;
var errors:Number         = 0;
var kbs:Number            = 0;
var allBytesLoaded:Number = 0;
var allBytesTotal:Number  = 0;
var allKbsAvg:Number      = 0;
var allowedTypes:Array;
var scriptURL:URLRequest;
var variables:URLVariables;
var queueReversed:Boolean = false;

// For debugging, alert any value to javascript
function debug(someValue) {
	ExternalInterface.call('alert("' + someValue + '")');
}

// Trigger a javascript event
function $trigger(eventName:String, ... args):void {
	// Add parenthesis
	function p(s:String):String {
		return ('('+s+')');
	}
	// Add quotes
	function q(s:String):String {
		return ('"'+s+'"');
	}
	var list:Array = [q(eventName)]; //Add the event to the array
	if (args.length > 0) list.push(JSON.encode(args)); // Add arguments to the array as a JSON object
	ExternalInterface.call(['jQuery'+p(q('#'+param.uploadifyID)), p(list.join(','))].join('.trigger')); // Trigger the event
}

// Random string generator for queue IDs
function generateID(len:Number):String {
	var chars:Array = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	var ID:String = '';
	var index:Number;
	for (var n:int = 0; n < len; n++) {
		ID += chars[Math.floor(Math.random() * 25)];
	}
	return ID;
}

// This is important for clicking the button correctly
browseBtn.buttonMode = true;
browseBtn.useHandCursor = true;
browseBtn.mouseChildren = false;

// Set the size of the button
function setButtonSize():void {
	if (param.hideButton) {
		browseBtn.width  = param.width;
		browseBtn.height = param.height;
	}
	// Set the size of the button on the page
	ExternalInterface.call('jQuery("#' + param.uploadifyID + '").attr("width",' + param.width + ')');
	ExternalInterface.call('jQuery("#' + param.uploadifyID + '").attr("height",' + param.height + ')');
}
//setButtonSize();

browseBtn.addEventListener(MouseEvent.ROLL_OVER, function (event:MouseEvent):void {
	browseBtn.gotoAndStop(2);
});
browseBtn.addEventListener(MouseEvent.ROLL_OUT, function (event:MouseEvent):void {
	browseBtn.gotoAndStop(1);
});
browseBtn.addEventListener(MouseEvent.MOUSE_DOWN, function (event:MouseEvent):void {
	browseBtn.gotoAndStop(3);
});

// create the scriptData variable if it doesn't exist
if (!param.scriptData) {
	param.scriptData = '';
}

// Limit the file types
function setAllowedTypes():void {
	allowedTypes = [];
	if (param.fileDesc && param.fileExt) {
		var fileDescs:Array = param.fileDesc.split('|');
		var fileExts:Array = param.fileExt.split('|');
		for (var n = 0; n < fileDescs.length; n++) {
			allowedTypes.push(new FileFilter(fileDescs[n], fileExts[n]));
		}
	}
}
setAllowedTypes();

// Set or get the variables
function uploadify_updateSettings(settingName:String, settingValue) {
	if(settingValue == null) {
		if (settingName == 'queueSize') {
			return fileQueue.length;
		}
		return param[settingName];
	} else {
		param[settingName] = settingValue;
		if(settingName == 'fileDesc' || settingName == 'fileExt') setAllowedTypes();
		return true;
	}
}

// Browse for Files
browseBtn.addEventListener(MouseEvent.CLICK, function():void {
	if (objSize(activeUploads) == 0) { // Don't browse if it's uploading
		if (!allowedTypes) {
			(!param.multi) ? fileRefSingle.browse() : fileRefMulti.browse();
		} else {
			(!param.multi) ? fileRefSingle.browse(allowedTypes) : fileRefMulti.browse(allowedTypes);
		}
	}
});

// Get the size of an object
function objSize(obj:Object):Number {
	var i:int = 0;
	for (var item in obj) {
		i++;
	}
	return i;
}

// Get actual folder path
function getFolderPath():String {
	var folder:String = param.folder;
	if (param.folder.substr(0,1) != '/' && param.folder.substr(0,4) != 'http') {
		folder = param.pagepath + param.folder;
		var folderParts:Array = folder.split('/');
		for (var i = 0; i < folderParts.length; i++) {
			if (folderParts[i] == '..') {
				folderParts.splice(i - 1, 2);
			}
		}
		folder = folderParts.join('/');
	}
	return folder;
}

// Get the array index of the item in the fileQueue
function getIndex(ID:String):Number {
	var index:int;
	for (var n:Number = 0; n < fileQueue.length; n++) {
		if (fileQueue[n].ID == ID) {
			index = n;
		}
	}
	return index;
}

// Check if a file with the same name is already in the queue
function inQueue(fileName:String):Object {
	var obj:Object = new Object();
	obj.testResult = false;
	if (fileQueue.length > 0) {
		for (var n = 0; n < fileQueue.length; n++) {
			if (fileQueue[n].file.name == fileName) {
				obj.size       = fileQueue[n].file.size;
				obj.ID         = fileQueue[n].ID;
				obj.arrIndex   = n;
				obj.testResult = true;
			}
		}
	}
	return obj;
}

// When selecting a file
function fileSelectSingleHandler(event:Event):void {
	// Check if the filename already exists in the queue
	fileItem = new Object();
	fileItem.file = FileReference(event.target);
	uploadify_clearFileUploadQueue(true);
	var ID:String = generateID(6);
	fileItem.ID = ID;
	fileQueue.push(fileItem);
	filesSelected = 1;
	allBytesTotal = fileItem.file.size;
	$trigger('uploadifySelect',ID,fileItem.file);
	$trigger('uploadifySelectOnce',{
		'fileCount'     : fileQueue.length,
		'filesSelected' : filesSelected,
		'filesReplaced' : filesReplaced,
		'allBytesTotal' : allBytesTotal
	});
	filesSelected = 0;
	filesReplaced = 0;
	if (param.auto) {
		if (param.checkScript) { 
			uploadify_uploadFiles(null, false);
		} else {
			uploadify_uploadFiles(null, true);
		}
	}
}

function fileSelectMultiHandler(event:Event):void {
	var ID:String = '';
	for (var n:Number = 0; n < fileRefMulti.fileList.length; n++) {
		fileItem = new Object();
		fileItem.file = fileRefMulti.fileList[n];
		// Check if the filename already exists in the queue
		var queueTest:Object = inQueue(fileRefMulti.fileList[n].name);
		if (queueTest.testResult) {
			allBytesTotal -= queueTest.size;
			allBytesTotal += fileItem.file.size;
			fileItem.ID    = fileQueue[queueTest.arrIndex].ID;
			fileQueue[queueTest.arrIndex] = fileItem;
			filesReplaced++;
		} else {
			if (fileQueue.length < param.queueSizeLimit) {
				ID = generateID(6);
				fileItem.ID = ID;
				fileQueue.push(fileItem);
				filesSelected++;
				allBytesTotal += fileItem.file.size;
				$trigger('uploadifySelect',ID,fileItem.file);
			} else {
				$trigger('uploadifyQueueFull',param.queueSizeLimit);
				break;
			}
		}
	}
	$trigger('uploadifySelectOnce',{
		'fileCount'     : fileQueue.length,
		'filesSelected' : filesSelected,
		'filesReplaced' : filesReplaced,
		'allBytesTotal' : allBytesTotal
	});
	filesSelected = 0;
	filesReplaced = 0;
	if (param.auto) {
		if (param.checkScript) { 
			uploadify_uploadFiles(null, false);
		} else {
			uploadify_uploadFiles(null, true);
		}
	}
}
fileRefSingle.addEventListener(Event.SELECT, fileSelectSingleHandler);
fileRefMulti.addEventListener(Event.SELECT, fileSelectMultiHandler);

// This function should run during upload so flash doesn't timeout
function uploadCounter(event:Event):void {
	counter++;
}

// Start the upload
function uploadify_uploadFiles(ID:String, checkComplete:Boolean):void {
	if (!queueReversed) {
		fileQueue.reverse();
		queueReversed = true;
	}
	if (param.script.substr(0,1) != '/' && param.script.substr(0,4) != 'http') param.script = param.pagepath + param.script;
	scriptURL = new URLRequest(param.script);
	variables = new URLVariables();
	(param.method.toUpperCase() == "GET") ? scriptURL.method = URLRequestMethod.GET : scriptURL.method = URLRequestMethod.POST;
	if (param.scriptData != '') variables.decode(unescape(param.scriptData));
	if (param.fileExt) variables.fileext = unescape(param.fileExt);
	variables.folder = unescape(getFolderPath());
	scriptURL.data = variables;
	if (param.checkScript && !checkComplete) {
		var fileQueueObj:Object = new Object();
		if (ID) {
			var index:int = getIndex(ID);
			if (fileQueue[index].file) {
				fileQueueObj[fileQueue[index].ID] = fileQueue[index].file.name;
			}
			$trigger('uploadifyCheckExist',param.checkScript,fileQueueObj,param.folder,true);
		} else {
			for (var n:Number = fileQueue.length - 1; n > -1; n--) {
				if (fileQueue[n]) {
					fileQueueObj[fileQueue[n].ID] = fileQueue[n].file.name;
				}
			}
			$trigger('uploadifyCheckExist',param.checkScript,fileQueueObj,param.folder,false);
		}
	} else {
		if (ID && fileQueue[getIndex(ID)].file) {
			uploadFile(fileQueue[getIndex(ID)].file, getIndex(ID), ID, true);
		} else {
			for (n = fileQueue.length - 1; n > -1; n--) {
				if (objSize(activeUploads) < parseInt(param.simUploadLimit)) {
					if (!activeUploads[fileQueue[n].ID] && fileQueue[n].file) {
						uploadFile(fileQueue[n].file, n, fileQueue[n].ID, false);
					}
				} else {
					break;
				}
			}
		}
	}
}

function queueIsNotEmpty(item:*, index:int, array:Array):Boolean {
	return (item.file != '');
}

// Upload each file
function uploadFile(file:FileReference, index:int, ID:String, single:Boolean):void {
	var startTimer:Number      = 0;
	var lastBytesLoaded:Number = 0;
	var kbsAvg:Number          = 0;
	
	function fileOpenHandler(event:Event) {
		startTimer = getTimer();
		$trigger('uploadifyOpen',ID,event.currentTarget);
	}
	
	function fileProgressHandler(event:ProgressEvent):void {
		var percentage:Number = Math.round((event.bytesLoaded / event.bytesTotal) * 100);
		if ((getTimer()-startTimer) >= 150) {
			kbs = ((event.bytesLoaded - lastBytesLoaded)/1024)/((getTimer()-startTimer)/1000);
			kbs = int(kbs*10)/10; 
			startTimer = getTimer();
			if (kbsAvg > 0) {
				kbsAvg = (kbsAvg + kbs)/2;
			} else {
				kbsAvg = kbs;
			}
			allKbsAvg = (allKbsAvg + kbsAvg)/2;
		}
		allBytesLoaded += (event.bytesLoaded - lastBytesLoaded);
		lastBytesLoaded = event.bytesLoaded;
		$trigger('uploadifyProgress',ID,event.currentTarget,{
			'percentage'     : percentage,
			'bytesLoaded'    : event.bytesLoaded,
			'allBytesLoaded' : allBytesLoaded,
			'speed'          : kbs
		});
	}
	
	function fileCompleteHandler(event:DataEvent):void {
		if (kbsAvg == 0) {
			kbs = (file.size/1024)/((getTimer()-startTimer)/1000);
			kbsAvg = kbs;
			allKbsAvg = (allKbsAvg + kbsAvg)/2;
		}
		
		allBytesLoaded -= lastBytesLoaded;
		allBytesLoaded += event.currentTarget.size;
		
		$trigger('uploadifyProgress',ID,event.currentTarget,{
			'percentage'     : 100,
			'bytesLoaded'    : event.currentTarget.size,
			'allBytesLoaded' : allBytesLoaded,
			'speed'          : kbs
		});
		$trigger('uploadifyComplete',ID,{
				'name'             : event.currentTarget.name,
				'filePath'         : getFolderPath() + '/' + event.currentTarget.name,
				'size'             : event.currentTarget.size,
				'creationDate'     : event.currentTarget.creationDate,
				'modificationDate' : event.currentTarget.modificationDate,
				'type'             : event.currentTarget.type
			},
			escape(event.data),{
			'fileCount' : (fileQueue.length-1),
			'speed'     : kbsAvg
		});
		filesUploaded++;
		fileQueue.splice(getIndex(ID),1);
		delete activeUploads[ID];
		if (!single) {
			uploadify_uploadFiles(null, true);
		}
		event.currentTarget.removeEventListener(DataEvent.UPLOAD_COMPLETE_DATA, fileCompleteHandler);
		if (!fileQueue.some(queueIsNotEmpty) && objSize(activeUploads) == 0) {
			$trigger('uploadifyAllComplete',{
				'filesUploaded'  : filesUploaded,
				'errors'         : errors,
				'allBytesLoaded' : allBytesLoaded,
				'speed'          : allKbsAvg
			});
			resetVars();
		}
	}
	
	// Add all the event listeners
	file.addEventListener(Event.OPEN, fileOpenHandler);
	file.addEventListener(ProgressEvent.PROGRESS, fileProgressHandler);
	file.addEventListener(DataEvent.UPLOAD_COMPLETE_DATA, fileCompleteHandler);
	
	// Reset all the numbers
	function resetVars() {
		filesUploaded  = 0;
		errors         = 0;
		allBytesLoaded = 0;
		allBytesTotal  = 0;
		allKbsAvg      = 0;
		filesChecked   = 0;
		queueReversed  = false;
	}
	
	// Handle all the errors
	file.addEventListener(HTTPStatusEvent.HTTP_STATUS, function(event:HTTPStatusEvent):void {
		if (errorArray.indexOf(ID) == -1) {  
			$trigger('uploadifyError',ID,event.currentTarget,{
				'type' : 'HTTP',
				'info' : event.status
			});
			finishErrorHandler(ID);
		}
	});
	file.addEventListener(IOErrorEvent.IO_ERROR, function(event:IOErrorEvent):void {
		if (errorArray.indexOf(ID) == -1) {
			$trigger('uploadifyError',ID,event.currentTarget,{
				'type' : 'IO',
				'info' : event.text
			});
			finishErrorHandler(ID);
		}
	});
	file.addEventListener(SecurityErrorEvent.SECURITY_ERROR, function(event:SecurityErrorEvent):void {
		if (errorArray.indexOf(ID) == -1) { 
			$trigger('uploadifyError',ID,event.currentTarget,{
				'type' : 'Security',
				'info' : event.text
			});
			finishErrorHandler(ID);
		}
	});
	
	// Common routines used by all errors
	function finishErrorHandler(ID:String) {
		errorArray.push(ID);
		fileQueue[getIndex(ID)].file = '';
		delete activeUploads[ID];
		if (!single) {
			uploadify_uploadFiles(null, true);
		}
		errors++;
		if (!fileQueue.some(queueIsNotEmpty)) {
			if (root.hasEventListener(Event.ENTER_FRAME)) {
				root.removeEventListener(Event.ENTER_FRAME, uploadCounter);
			}
			$trigger('uploadifyAllComplete',{
				'filesUploaded'  : filesUploaded,
				'errors'         : errors,
				'allBytesLoaded' : allBytesLoaded,
				'speed'          : allKbsAvg
			});	
			resetVars();
		}
	}
	
	if (param.sizeLimit && file.size > parseInt(param.sizeLimit)) {
		if (errorArray.indexOf(ID) == -1) { 
			$trigger('uploadifyError',ID,file,{
				'type' : 'File Size',
				'info' : param.sizeLimit
			});
			finishErrorHandler(ID);
		}
	} else {
		file.upload(scriptURL, param.fileDataName);
		activeUploads[ID] = true;
	}
}

function uploadify_cancelFileUpload(ID:String, single:Boolean, clearFast:Boolean):void {
    var index:int = getIndex(ID);
    var fileObj:Object = new Object();
    if (fileQueue[index].file) {
        fileObj = fileQueue[index].file;
        fileQueue[index].file.cancel();
        allBytesTotal -= fileQueue[index].file.size;
    }

    fileQueue.splice(index,1);

    if (activeUploads[ID]) {
        delete activeUploads[ID];
        uploadify_uploadFiles(null, true);
        if (root.hasEventListener(Event.ENTER_FRAME) && objSize(activeUploads) == 0) {
            root.removeEventListener(Event.ENTER_FRAME, uploadCounter);
        }
    }

    $trigger('uploadifyCancel',ID,fileObj,{
        'fileCount'     : (fileQueue.length),
        'allBytesTotal' : allBytesTotal
    },clearFast);
}

// Cancel all uploads
function uploadify_clearFileUploadQueue(clearFast:Boolean):void {
	if (!queueReversed) {
		fileQueue.reverse();
		queueReversed = true;
	}
	for (var n:Number = fileQueue.length - 1; n >= 0; n--) {
		uploadify_cancelFileUpload(fileQueue[n].ID, false, clearFast);
	}
	if (root.hasEventListener(Event.ENTER_FRAME)) {
		root.removeEventListener(Event.ENTER_FRAME, uploadCounter);
	}
	$trigger('uploadifyClearQueue');
	filesUploaded  = 0;
	errors         = 0;
	allBytesLoaded = 0;
	allBytesTotal  = 0;
	allKbsAvg      = 0;
	filesChecked   = 0;
	queueReversed  = false;
}

// Create all the callbacks for the functions
ExternalInterface.addCallback('updateSettings', uploadify_updateSettings);
ExternalInterface.addCallback('startFileUpload', uploadify_uploadFiles);
ExternalInterface.addCallback('cancelFileUpload', uploadify_cancelFileUpload);
ExternalInterface.addCallback('clearFileUploadQueue', uploadify_clearFileUploadQueue);