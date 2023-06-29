export function getFullDate(date) {
  const userDateObject = new Date(date);
  const userMonth = `0${userDateObject.getMonth() + 1}`;
  const userDate = `${userDateObject.getDate()}:${userMonth.slice(-2)}:${userDateObject.getFullYear()}`;
  return userDate;
}

export function getFullTime(date) {
  const userDateObject = new Date(date);
  const userHours = `0${userDateObject.getHours()}`;
  const userMinutes = `0${userDateObject.getMinutes()}`;
  const userTime = `${userHours.slice(-2)}:${userMinutes.slice(-2)}`;
  return userTime;
}

export function alertSeverityCode(alertType){
  // return alertType === 'Critical'? 1 : alertType === 'Warning'? 2 : alertType === 'outOfRange'? 3 : 
  // alertType === 'Stel'? 4 : 5; // Commented on 20-02-2023 (Bug - I-D132)
  return alertType === 'Stel'? 1 : alertType === 'Critical'? 2 : alertType === 'TWA'? 3 : 
  alertType === 'Warning'? 4 :alertType === 'deviceDisconnected'? 5 : alertType === 'outOfRange'? 6 : 7;
}

// function handleSwitchcase(alertStatus, colorCode, defaultCase, case1, case2, case3){
//   let newColorCode = colorCode || '#a5d6a7';
//   switch(alertStatus){
//     case 1 : newColorCode = case1 || '#ef9a9a';
//       break;
//     case 2 : newColorCode = case2 || '#ffb74d';
//       break;
//     case 3 : newColorCode = case3 || '#ce93d8';
//       break;
//     default : newColorCode = defaultCase || '#a5d6a7';
//       break;
//   }
//   return newColorCode;
// } // 24-02-2023

function handleSwitchcase(alertStatus, colorCode, defaultCase, case1, case2, case3){
  let newColorCode = colorCode || '#a5d6a7';
  switch(alertStatus){
    case 1 : newColorCode = case1 || '#ef9a9a';
      break;
    case 2 : newColorCode = case1 || '#ef9a9a';
    break;
    case 3 : newColorCode = case2 || '#ffb74d';
      break;
    case 4 : newColorCode = case2 || '#ffb74d';
    break;
    case 5 : newColorCode = case3 || '#ce93d8';
      break;
    default : newColorCode = defaultCase || '#a5d6a7';
      break;
  }
  return newColorCode;
}

export function setAlertColor(newNotificationStack){
  // if (newNotificationStack.length > 0) {
  //   let colorCode = {
  //     priority: 6,
  //     color: '#ab47bc',
  //     message: 'Alert'
  //   };
  //   for(let i = 0; i < newNotificationStack.length; i++) {
  //     if (newNotificationStack[i].alertType === 'Critical') {
  //       colorCode = {
  //         priority: 1,
  //         color: '#E53935',
  //         message: 'Critical Alert'
  //       };
  //       break;
  //     } else if(newNotificationStack[i].alertType === 'Warning' && colorCode.priority > 2) {
  //       colorCode = {
  //         priority: 2,
  //         color: '#ff9800',
  //         message: 'Warning Alert'
  //       }
  //     } else if(newNotificationStack[i].alertType === 'outOfRange' && colorCode.priority > 3) {
  //       colorCode = {
  //         priority: 3,
  //         color: '#ab47bc',
  //         message: 'Out Of Range Alert'
  //       }
  //     } else if(newNotificationStack[i].alertType === 'Stel' && colorCode.priority > 4) {
  //       colorCode = {
  //         priority: 4,
  //         color: '#E53935',
  //         message: 'STEL Alert'
  //       }
  //     } else if(newNotificationStack[i].alertType === 'TWA' && colorCode.priority > 5) {
  //       colorCode = {
  //         priority: 5,
  //         color: '#ff9800',
  //         message: 'TWA Alert'
  //       }
  //     }
  //   }
  //   return colorCode;
  // } // Commented on 20-02-2023 (Bug - I-D132)
  if (newNotificationStack.length > 0) {
    let colorCode = {
      priority: 7,
      color: '#ab47bc',
      message: 'Alert'
    };
    for(let i = 0; i < newNotificationStack.length; i++) {
      if (newNotificationStack[i].alertType === 'Stel') {
        colorCode = {
          priority: 1,
          color: '#E53935',
          message: 'STEL Alert'
        };
        break;
      } else if(newNotificationStack[i].alertType === 'Critical' && colorCode.priority > 2) {
        colorCode = {
          priority: 2,
          color: '#E53935',
          message: 'Critical Alert'
        }
      } else if(newNotificationStack[i].alertType === 'TWA' && colorCode.priority > 3) {
        colorCode = {
          priority: 3,
          color: '#ff9800',
          message: 'TWA Alert'
        }
      } else if(newNotificationStack[i].alertType === 'Warning' && colorCode.priority > 4) {
        colorCode = {
          priority: 4,
          color: '#ff9800',
          message: 'Warning Alert'
        }
      }else if(newNotificationStack[i].alertType === 'deviceDisconnected' && colorCode.priority > 5) {
        colorCode = {
          priority: 5,
          color: '#7d715f',
          message: 'Device Disconnected'
        }
      } else if(newNotificationStack[i].alertType === 'outOfRange' && colorCode.priority > 6) {
        colorCode = {
          priority: 6,
          color: '#ab47bc',
          message: 'Out Of Range Alert'
        }
      }
    }
    return colorCode;
  }
}

export function getDeviceBackgroundColor(deviceMode, alertStatus, disconnectedStatus){
  let colorCode = '#a5d6a7';
    if(deviceMode === 'disabled' || disconnectedStatus === 1) {
      colorCode = '#9e9e9e';
    } else {
      if(deviceMode === 'bumpTest' || deviceMode === 'calibration' || deviceMode === 'firmwareUpgradation' || deviceMode === 'config'){
        colorCode = '#f8bbd0';
      } else if(deviceMode === 'enabled'){
        colorCode = handleSwitchcase(alertStatus, colorCode, '#a5d6a7', '#ef9a9a', '#ffb74d', '#ce93d8');
      }
    }
  return colorCode;
}

export function getDeviceHeaderColor(deviceMode, alertStatus, disconnectedStatus){
  let colorCode = '#212121';
  if(deviceMode === 'disabled' || disconnectedStatus === 1) {
    colorCode = '#212121';
  } else {
    if(deviceMode === 'bumpTest' || deviceMode === 'calibration' || deviceMode === 'firmwareUpgradation' || deviceMode === 'config'){
      colorCode = '#c2185b';
    } else if(deviceMode === 'enabled'){
      colorCode = handleSwitchcase(alertStatus, colorCode, '#1b5e20', '#b71c1c', '#e65100', '#4a148c');
    }
  }
  return colorCode;
}

export function getDeviceModeColor(deviceMode){
  let colorCode = '#212121';
  if(deviceMode === 'disabled') {
    colorCode = '#212121';
  } else {
    if(deviceMode === 'bumpTest' || deviceMode === 'calibration' || deviceMode === 'firmwareUpgradation' || deviceMode === 'config'){
      colorCode = '#c2185b';
    } else if(deviceMode === 'enabled'){
      colorCode = '#1b5e20';
    }
  }
  return colorCode;
}

export function getSensorBackgroundColor(sensorStatus, alertStatus){
  let colorCode = '#a5d6a7';
    if(sensorStatus === '0') {
      colorCode = '#9e9e9e';
    } else {
      colorCode = handleSwitchcase(alertStatus, colorCode, '#a5d6a7', '#ef9a9a', '#ffb74d', '#ce93d8');
    }
  return colorCode;
}

export function getSensorHeaderColor(sensorStatus, alertStatus){
  let colorCode = '#212121';
    if(sensorStatus === '0') {
      colorCode = '#212121';
    } else {
      colorCode = handleSwitchcase(alertStatus, colorCode, '#1b5e20', '#b71c1c', '#e65100', '#4a148c');
    }
  return colorCode;
}

// export function setAlertStatusCode(element, data, setAlertStatus){
//   if(element.alertPriority > data.alertPriority){
//     switch(data.alertType){
//       case 'Critical' : setAlertStatus(1);
//       break;
//       case 'Warning' : setAlertStatus(2);
//       break;
//       case 'outOfRange' : setAlertStatus(3);
//       break;
//       default : break;
//     }
//   }
// } // 24-02-2023

export function setAlertStatusCode(element, data, setAlertStatus){
  if(element.alertPriority > data.alertPriority){
    switch(data.alertType){
      case 'Stel' : setAlertStatus(1);
      break;
      case 'Critical' : setAlertStatus(2);
      break;
      case 'TWA' : setAlertStatus(3);
      break;
      case 'Warning' : setAlertStatus(4);
      break;
      case 'deviceDisconnected' : setAlertStatus(5);
      break;
      case 'outOfRange' : setAlertStatus(6);
      break;
      default : break;
    }
  }
}

export function setAlertPriorityAndType(element, data){
  return element.alertPriority < data.alertPriority ? element : 
  {
    alertLabel: data.alertType === 'Critical' ? 'Critical' : data.alertType === 'outOfRange' ? 'Out Of Range' : data.alertType === 'Warning' ? 
    'Warning' : data.alertType === 'TWA' ? 'TWA': data.alertType === 'Stel' ? 'STEL' :  data.alertType === 'deviceDisconnected'? 'Disconnected' : 'Good',
    
    alertColor: data.alertType === 'Critical' ? 'red' : data.alertType === 'outOfRange' ? '#9c27b0' : data.alertType === 'Warning' ? 
    'orange' : data.alertType === 'TWA' ? 'orange': data.alertType === 'Stel' ? 'red' : data.alertType === 'deviceDisconnected' ? 'gray' : 'green',
    // alertPriority: data.alertType === 'Critical' ? 1 : data.alertType === 'Warning' ? 2 : data.alertType === 'outOfRange' ?
    //  3: data.alertType === 'Stel' ? 4 : data.alertType === 'TWA' ? 5 : 6, // Commented on 20-02-2023 (Bug - I-D132)
    
    alertPriority: data.alertType === 'Stel' ? 1 : data.alertType === 'Critical' ? 2 : data.alertType === 'TWA' ?
     3: data.alertType === 'Warning' ? 4 : data.alertType === 'deviceDisconnected' ? 5 : data.alertType === 'outOfRange' ? 6 : 7,
  };
}

export function setAlertPriorityStatus(element, alertType) {
  if (!alertType) {
    element.alertLabel = 'Good';
    element.alertColor = 'green';
  } else {
    switch (alertType) {
      case 'Critical':
        element.alertLabel = 'Critical';
        element.alertColor = 'red';
        break;
      case 'outOfRange':
        element.alertLabel = 'Out Of Range';
        element.alertColor = '#9c27b0';
        break;
      case 'Warning':
        element.alertLabel = 'Warning';
        element.alertColor = 'orange';
        break;
      case 'TWA':
        element.alertLabel = 'TWA';
        element.alertColor = 'orange';
        break;
      case 'Stel':
        element.alertLabel = 'STEL';
        element.alertColor = 'red';
        break;
      case 'deviceDisconnected':
        element.alertLabel = 'Disconnected';
        element.alertColor = 'gray';
        break;
      default:
        element.alertLabel = 'Good';
        element.alertColor = 'green';
        break;
    }
  }

  return element;
}

export function setAQIColor(value){
  var color = '#d4086e';
  if(value === 'NA'){
    color = '#6b6a6a';
  } else if(value>0){
    if(value<=50){
      color = '#026107';
    } else if(value<=100){
      color = '#729c02';
    // } else if(value<=150){
    //   color = '#f5f50a';
    } else if(value<=200){
      color = '#dbc604';
    } else if(value<=300){
      color = '#f78e16';
    } else if (value<=400){
      color = '#ed120e';
    } 
    else {
      color = '#af2d24';
    }
  }else{
    color = '#d4086e';
  }
  return color;
}

export function setAQILabel(value){
  var label = 'NA';
  if(value>0){
    if(value<=50){
      label = 'Good';
    } else if(value<=100){
      label = 'Satisfactory';
    // } else if(value<=150){
    //   color = '#f5f50a';
    } else if(value<=200){
      label = 'Moderate';
    } else if(value<=300){
      label = 'Poor';
    } else if (value<=400){
      label = 'Very poor';
    } 
    else {
      label = 'Severe';
    }
  }else{
    label = 'NA';
  }
  return label;
}

export function dateRangevalidator(setNotification){
  setNotification({
    status: true,
    type: 'error',
    message: 'Please select the proper date range',
  });
}

export function currentDateValidator(){
  const currentTime = new Date();
  const date = currentTime.toLocaleDateString('es-CL')
  const dateFormat = date?.split('-');
  const dateValue = `${dateFormat[2]}-${dateFormat[1]}-${dateFormat[0]}`;
  return dateValue;
}

export const acknowledgedAlertvalidator = (alertList, lastAcknowledgedAlert) => {
  if(alertList[0].id !== lastAcknowledgedAlert.id){
    if(dateFormateConverter(lastAcknowledgedAlert.dateTime) < dateFormateConverter(alertList[0].dateTime) ){
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

export const dateFormateConverter = (date) => {
  let convertedDate = '';
  if(date){
    const dateTime = date.split(' ');
    const dateValue = dateTime[0].split('-');
    const formatedDate = `${dateValue[2]}-${dateValue[1]}-${dateValue[0]}`;
    convertedDate = `${formatedDate} ${dateTime[1]}`;
  }
  return new Date(convertedDate);
}