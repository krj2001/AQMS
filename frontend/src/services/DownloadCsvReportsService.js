/* eslint-disable max-len */
import ApplicationStore from '../utils/localStorageUtil';

const _fetchServiceDownloadCsvData = (PATH, serviceMethod, data, successCallback, errorCallBack, fileName, reportType) => {
  const { user_token, userDetails } = ApplicationStore().getStorage('userDetails');
 // const END_POINT = 'https://localhost/backend/api/';
  const END_POINT = `${process.env.REACT_APP_API_ENDPOINT}api/`;
  const { emailId, userRole, companyCode } = userDetails;

  const headers = {
    'Content-Type': 'blob',
    authorization: `Bearer ${user_token}`,
    companyCode: `${companyCode}`,
    userId: `${emailId}`,
    userRole: `${userRole}`,
    responseType: 'arraybuffer',
  };

  const body = (serviceMethod === 'GET') || (serviceMethod === 'DELETE') ? {} : { body: JSON.stringify(data) };

  const bodyParameters = {
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    redirect: 'follow',
    referrerPolicy: 'no-referrer',
    headers,
    ...body,
  };

  const bodyObject = {
    method: serviceMethod,
    ...bodyParameters,
  };

  let filename = '';

  return fetch(END_POINT + PATH, bodyObject)
    .then((response) => {
      if (!response.ok) {
        throw Error(response.statusText);
      }

      // const contentDisposition = response.headers.get('Content-Disposition');
      // filename = contentDisposition.split(';')[1].split('filename')[1].split('=')[1].trim();
      filename = fileName || 'Reports.xlsx';
      // var filename = `${Date.now()}.xlsx`; //contentDisposition.split(';')[1].split('filename')[1].split('=')[1].trim();
      if(reportType === 'download'){
        return response.blob();
      } else if(reportType === 'email'){
        return response.json();
      }
    })
    .then((dataResponse) => {
      successCallback(dataResponse);
      if(reportType === 'download'){
        if (dataResponse != null) {
          const url = window.URL.createObjectURL(dataResponse);
          const a = document.createElement('a');
          a.href = url;
          a.download = filename;
          document.body.appendChild(a);
          a.click();
          // a.remove();
        }
      } else if(reportType === 'email'){
        //
      }
    })
    .catch((error) => {
      error.errorObject.then((errorResponse) => {
        if (error.errorStatus === 401 && errorResponse.message === 'Unable to access the page, Token Expired') {
          ApplicationStore().clearStorage();
          /* eslint-disable-next-line */
          location.reload();
        }
        errorCallBack(error.errorStatus, errorResponse.message);
      });
    });
};

export const DownloadReportAlarmCsv = (data, successCallback, errorCallBack) => {
  const { location_id, branch_id, facility_id, building_id, floor_id, lab_id, deviceId, fromDate, toDate } = data;
  return _fetchServiceDownloadCsvData(`exportAlarm?=&location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}&deviceId=${deviceId}&fromDate=${fromDate}&toDate=${toDate}`, 'GET', {}, successCallback, errorCallBack, 'Alarms Report.xlsx', 'download');
};

export const DownloadReportBumpTestCsv = (data, successCallback, errorCallBack) => {
  const { location_id, branch_id, facility_id, building_id, floor_id, lab_id, device_id, fromDate, toDate} = data;
  return _fetchServiceDownloadCsvData(`exportBumpTestCsv?=&location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}&deviceId=${device_id}&fromDate=${fromDate}&toDate=${toDate}`, 'GET', {}, successCallback, errorCallBack, 'Bump Test Report.xlsx', 'download');
};

export const DownloadReportAqiStatusCsv = (data, successCallback, errorCallBack) => {
  // const deviceId = data.device_id;
  const { location_id } = data;
  const { branch_id } = data;
  const { facility_id } = data;
  const { building_id } = data;
  const { floor_id } = data;
  const { lab_id } = data;
  return _fetchServiceDownloadCsvData(`exportAqiStatusReport?=&location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}`, 'GET', {}, successCallback, errorCallBack);
};

export const DownloadReportSensorLogCsv = (data, successCallback, errorCallBack) => {
  const { location_id, branch_id, facility_id, building_id, floor_id, lab_id, deviceId, fromDate, toDate } = data;
  return _fetchServiceDownloadCsvData(`exportSensorLogCsv?=&location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}&deviceId=${deviceId}&fromDate=${fromDate}&toDate=${toDate}`, 'GET', {}, successCallback, errorCallBack, 'Limit edit Logs Report.xlsx', 'download');
};

export const DownloadReportAqiCsv = (data, successCallback, errorCallBack) => {
  const { location_id, branch_id, facility_id, building_id, floor_id, lab_id, fromDate, toDate, deviceId} = data;
  return _fetchServiceDownloadCsvData(`aqiReportExport?location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}&deviceId=${deviceId}&fromDate=${fromDate}&toDate=${toDate}`, 'GET', {}, successCallback, errorCallBack, 'AQI Report.xlsx', 'download');
};

export const serverUtiliExport = (data, successCallback, errorCallBack) => {
  const { fromDate } = data;
  const { toDate } = data;
  return _fetchServiceDownloadCsvData(`serverUtiliExport?=&fromDate=${fromDate}&toDate=${toDate}`, 'GET', {}, successCallback, errorCallBack, 'Server Utilization Report.xlsx', 'download');
};

export const DownloadAppVersionReport = (successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`appVersionExport`, 'GET', {}, successCallback, errorCallBack, 'Application Version Report.xlsx', 'download');
};

export const DownloadFirmwareVersionReport = (data, successCallback, errorCallBack) => {
  const { location_id, branch_id, facility_id, building_id, floor_id, lab_id, deviceId} = data;
  return _fetchServiceDownloadCsvData(`firmwareExport?&location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}&deviceId=${deviceId}`, 'GET', {}, successCallback, errorCallBack, 'Firmware Version Report.xlsx', 'download');
};

export const DownloadHardwareVersionReport = (data, successCallback, errorCallBack) => {
  const { location_id, branch_id, facility_id, building_id, floor_id, lab_id, deviceId} = data;
  return _fetchServiceDownloadCsvData(`exportHardwareVersionLogs?&location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}&deviceId=${deviceId}`, 'GET', {}, successCallback, errorCallBack, 'Hardware Model Version Report.xlsx', 'download');
};

export const DownloadSensorStatusCsv = (data, successCallback, errorCallBack) => {
  const { location_id, branch_id, facility_id, building_id, floor_id, lab_id, deviceId, fromDate, toDate} = data;
  return _fetchServiceDownloadCsvData(`sensorStatusReportExport?&location_id=${location_id}&branch_id=${branch_id}&facility_id=${facility_id}&building_id=${building_id}&floor_id=${floor_id}&lab_id=${lab_id}&deviceId=${deviceId}&fromDate=${fromDate}&toDate=${toDate}`, 'GET', {}, successCallback, errorCallBack, 'Sensor Status Report.xlsx', 'download');
};

// -----------------------------------------//
// ---------------  Email ------------------//
// -----------------------------------------//

// --------------------------- Alarm Email ------------------- //

export const EmailAlarmReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`alarmReportMailExcelFile?location_id=${data.location_id}&deviceId=${data.deviceId}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

// ------------------------- AQI Index Email ---------------- //

export const EmailAQIIndexReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`aqiReportMailExcelFile?location_id=${data.location_id}&deviceId=${data.deviceId}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

// ------------------------- AQI Index Email ---------------- //

export const EmailSensorStatusReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`emailSensorStatusReport?location_id=${data.location_id}&deviceId=${data.deviceId}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

// ------------------------- Device Log Email ---------------- //

export const EmailDeviceLogReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`emailDeviceLog?location_id=${data.location_id}&deviceId=${data.deviceId}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

// ------------------------- Server Utilization Email ---------------- //

export const EmailServerUtilizationReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`emailServerUtilization?location_id=${data.location_id}&deviceId=${data.deviceId}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

// ------------------------- Firmware Version Email ---------------- //

export const EmailFirmwareVersionReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`emailFirmwareVersion?location_id=${data.location_id}&deviceId=${data.deviceId}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

// ------------------------- Hardware Model Version Email ---------------- //

export const EmailHardwareVersionReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`emailHardwareVersionLog?location_id=${data.location_id}&deviceId=${data.deviceId}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

// ------------------------- Bumptest Email ---------------- //

export const EmailBumptestReportService = (data, successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`emailBumpTest?location_id=${data.location_id}&deviceId=${data.device_id}&fromDate=${data.fromDate}&toDate=${data.toDate}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id=${data.floor_id}&lab_id=${data.lab_id}`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};


// ------------------------- Application Version Email ---------------- //

export const EmailAppVersionReportService = (successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`emailApplicationVersion`, 'GET', {}, successCallback, errorCallBack, '', 'email');
};

export const  DownloadCalibrationReportService = (data,successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`calibrationReport/download?location_id=${data.location_id}&branch_id=${data.branch_id}&facility_id=${data.facility_id}&building_id=${data.building_id}&floor_id&lab_id${data.floor_id}&device_id=${data.device_id}&fromDate=${data.fromDate}&toDate=${data.toDate}`, 'GET', {}, successCallback, errorCallBack, '', 'download');
};

// ----------- EventLogExport

export const  DownloadEventLogExportService = (data,successCallback, errorCallBack) => {
  return _fetchServiceDownloadCsvData(`eventLogExport?locationId=${data.location_id}&branchId=${data.branch_id}&facilityId=${data.facility_id}&buildingId=${data.building_id}&floorId=${data.floor_id}&fromDate=${data.fromDate}&toDate=${data.toDate}&eventName=${data.eventName}`, 'GET', {}, successCallback, errorCallBack, '', 'download');
};
