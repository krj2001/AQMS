import React, { useState, useEffect } from 'react';
import {
  InputLabel, MenuItem, FormControl, Select, TextField, Button, Typography, Grid, CircularProgress,
} from '@mui/material';
import DownloadIcon from '@mui/icons-material/Download';
import SendIcon from '@mui/icons-material/Send';
import { DataGrid } from '@mui/x-data-grid';
import { FetchAlarmReportDetails } from '../../services/LoginPageService';
import { DownloadReportAlarmCsv, EmailAlarmReportService } from '../../services/DownloadCsvReportsService';
import NotificationBar from '../notification/ServiceNotificationBar';
import { currentDateValidator, dateRangevalidator } from '../../utils/helperFunctions';

function Alarm({ siteId, deviceList }) {
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');
  const [deviceId, setDeviceId] = useState('');
  const [isLoading, setGridLoading] = useState(false);
  const [alarmReportList, setAlarmReportList] = useState([]);
  const [unTaggedAlarmReportList, setUnTaggedAlarmReportList] = useState();
  const [page, setPage] = useState(0);
  const [pageSize, setPageSize] = useState(10);
  const [rowCountState, setRowCountState] = useState(0);
  const [enableSend, setEnableSend] = useState(false);
  const [enableDownload, setEnableDownload] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    fetchNewData();
  }, [unTaggedAlarmReportList, page]);

  const dateFormat = (value) => {
    const date = value.split('-');
    const dateValue = `${date[2]}-${date[1]}-${date[0]}`;
    return dateValue;
  };

  const fetchDate = (value) => {
    if(value){
      const dateTime = value.split(' ');
      const date = dateTime[0].split('-');
      const dateValue = `${date[2]}-${date[1]}-${date[0]}`;
      return dateValue + ' | ' + dateTime[1];
    } else {
      return 'N/A'
    }
  };

  const fetchTime = (value) => {
    // if(value){
    //   const dateTime = value.split(' ');
    //   return dateTime[0];
    // } else {
    //   return 'N/A'
    // }
    if(value.alertTriggeredDuration){
      return 'A'
    }
    return 'N/A'
  };

  const columns = [
    {
      field: 'a_date',
      headerName: 'From Date & Time',
      minWidth: 180,
      // maxWidth: 130,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      renderCell: (params) => (
        <span>
          {
            dateFormat(params.row.a_date)
          }
          &nbsp;
          |
          &nbsp;
          {params.row.a_time}
        </span>
      ),
    },
    // {
    //   field: 'a_time',
    //   headerName: 'From Time',
    //   minWidth: 100,
    //   maxWidth: 120,
    //   align: 'center',
    //   flex: 1,
    //   headerAlign: 'center',
    // },
    // {
    //   field: 'alertTriggeredDuration',
    //   headerName: 'To Date',
    //   minWidth: 180,
    //   flex: 1,
    //   headerAlign: 'center',
    //   align: 'center',
    // },
    {
      // field: 'alertTriggeredDuration',
      headerName: 'To Date & Time',
      minWidth: 180,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
      renderCell: (params) => (
        <span>
          {
            fetchDate(params.row.alertTriggeredDuration)
          }
        </span>
      ),
    },
    {
      field: 'stateName',
      headerName: 'Location',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'branchName',
      headerName: 'Branch',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'facilityName',
      headerName: 'Facility',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'buildingName',
      headerName: 'Building',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'floorName',
      headerName: 'Floor',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'labDepName',
      headerName: 'Zone',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'deviceName',
      headerName: 'Devices',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'sensorTag',
      headerName: 'Sensor',
      minWidth: 130,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'alertStandardMessage',
      headerName: 'Alarm Type',
      minWidth: 150,
      maxWidth: 200,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'msg',
      headerName: 'Message',
      minWidth: 250,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'Reason',
      headerName: 'Reason',
      minWidth: 250,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
      renderCell: (params) => (
        <span>
          {params.row.alarmType === 'Latch' ? params.value : 'NA'}
        </span>
      ),
    },
    {
      field: 'userEmail',
      headerName: 'User',
      minWidth: 120,
      maxWidth: 150,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
    },
  ];

  const HandleDeviceChange = (deviceId) => {
    setDeviceId(deviceId);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    fromDate > toDate ? dateRangevalidator(setNotification) : fetchNewData();
  };

  const onPageSizeChange = (newPageSize) => {
    setPageSize(newPageSize);
  };

  const DownloadCsv = () => {
    if (fromDate !== '' && toDate !== '') {
      fromDate > toDate ? dateRangevalidator(setNotification) :
      (setEnableDownload(true),
      DownloadReportAlarmCsv({
        ...siteId, deviceId, fromDate, toDate,
      }, csvReportHandleSuccess, csvReportHandleException));
    } else {
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select a date range',
      });
    }
  };

  const csvReportHandleSuccess = () => {
    setTimeout(() => {
      setEnableDownload(false);
    }, 2000);
  };

  const csvReportHandleException = () => {
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'error',
        message: 'Something went wrong...',
      });
    }, 2000);
  };

  const fetchNewData = () => {
    if (fromDate !== '' && toDate !== '') {
      setGridLoading(true);
      FetchAlarmReportDetails({
        page, pageSize, ...siteId, deviceId, fromDate, toDate,
      }, AlarmReportHandleSuccess, AlarmReportHandleException);
    }
  };

  const AlarmReportHandleSuccess = (dataObject) => {
    setAlarmReportList(dataObject.data.data);
    setRowCountState(dataObject.data.totalRowCount);
    setGridLoading(false);
  };

  const AlarmReportHandleException = () => { };

  const handleCancel = () => {
    setFromDate('');
    setToDate('');
    setDeviceId('');
    setGridLoading(false);
    setUnTaggedAlarmReportList(!unTaggedAlarmReportList);
  };

  const onPageChange = (newPage) => {
    setPage(newPage);
  };

  const SendEmail = () => {
    if (fromDate !== '' && toDate !== '') {
      fromDate > toDate ? dateRangevalidator(setNotification) :
      (setEnableSend(true),
      EmailAlarmReportService({
        ...siteId, deviceId, fromDate, toDate,
      }, handleEmailSuccess, handleEmailException));
    } else {
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select a date range',
      });
    }
  };

  const handleEmailSuccess = (dataObject) => {
    setTimeout(() => {
      setEnableSend(false);
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message,
      });
    }, 2000);
  };

  const handleEmailException = (errorObject, errorMessage) => {
    setTimeout(() => {
      setEnableSend(false);
      setNotification({
        status: true,
        type: 'error',
        message: errorMessage,
      });
    }, 2000);
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  return (
    <Grid item>
      <form onSubmit={handleSubmit}>
        <Grid container spacing={2}>
              <Grid item                   
                  xs={12}
                  sm={4}
                  md={4}
                  lg={4}
                  xl={4}>
                <TextField
                  fullWidth
                  label="From Date"
                  type="date"
                  value={fromDate}
                  variant="standard"
                  required
                  onChange={(e) => {
                    setFromDate(e.target.value);
                  }}
                  autoComplete="off"
                  InputLabelProps={{
                    shrink: true, style: { fontFamily: 'customfont' }
                  }}
                  inputProps={{
                    max: currentDateValidator()
                  }}
                />
              </Grid>
              <Grid item                   
                  xs={12}
                  sm={4}
                  md={4}
                  lg={4}
                  xl={4}>
                <TextField
                  fullWidth
                  label="To Date"
                  type="date"
                  value={toDate}
                  variant="standard"
                  required
                  onChange={(e) => {
                    setToDate(e.target.value);
                  }}
                  autoComplete="off"
                  InputLabelProps={{
                    shrink: true, style: { fontFamily: 'customfont' }
                  }}
                  inputProps={{
                    max: currentDateValidator()
                  }}
                />

              </Grid>
              <Grid item 
                xs={12}
                sm={4}
                md={4}
                lg={4}
                xl={4}
              >
                <FormControl fullWidth>
                  <InputLabel sx={{ fontFamily: 'customfont', color: 'black' }}>Devices</InputLabel>
                  <Select
                    value={deviceId}
                    label="Devices"
                    variant="standard"
                    onChange={(e) => {
                      HandleDeviceChange(e.target.value);
                    }}
                  >
                    <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                      <em className={'font-bold'}>All</em>
                    </MenuItem>
                    {deviceList?.map((data, index) => (
                      <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.deviceName}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>
                <Grid item 
                  xs={12}
                  sm={3}
                  md={3}
                  lg={3}
                  xl={3}
                 className={'self-center'}>
                  <FormControl fullWidth>
                    <Button
                      sx={{
                        height: '0',
                        padding: "10px 19px",
                        color: 'white',
                        marginTop: '20px',
                        marginBottom: '15px',
                        fontSize: '13px',
                        borderRadius: '10px',
                        fontWeight: '600',
                        fontFamily: 'customfont',
                        letterSpacing: '1px'
                      }}
                      style={{
                        background: 'rgb(120 53 15)',}}
                      autoFocus type="submit">
                      Submit
                    </Button>
                  </FormControl>
                </Grid>
                <Grid item 
                  xs={12}
                  sm={3}
                  md={3}
                  lg={3}
                  xl={3}
                 className={'self-center'}>
                  <FormControl fullWidth>
                    <Button
                      sx={{
                        height: '0',
                        padding: "10px 19px",
                        color: 'white',
                        marginTop: '20px',
                        marginBottom: '15px',
                        fontSize: '13px',
                        borderRadius: '10px',
                        fontWeight: '600',
                        fontFamily: 'customfont',
                        letterSpacing: '1px'
                      }}
                      style={{
                        background: 'rgb(120 53 15)',}}
                      autoFocus onClick={handleCancel}>
                      Cancel
                    </Button>
                  </FormControl>
                </Grid>
                <Grid item 
                  xs={12}
                  sm={3}
                  md={3}
                  lg={3}
                  xl={3}
                 className={'self-center'}>
                  <FormControl fullWidth>
                    <Button
                      sx={{
                        height: '0',
                        padding: "9px 19px",
                        color: 'white',
                        marginTop: '20px',
                        marginBottom: '15px',
                        fontSize: '13px',
                        borderRadius: '10px',
                        fontWeight: '600',
                        fontFamily: 'customfont',
                        letterSpacing: '1px'
                      }}
                      style={{
                        background: 'rgb(19, 60, 129)',}}
                      autoFocus
                      onClick={() => {
                        DownloadCsv();
                      }}
                      endIcon={enableDownload === true ? <CircularProgress className={'h-6 w-6'} /> : <DownloadIcon />}
                      disabled={enableDownload}
                    >
                      Download
                    </Button>
                  </FormControl>
                </Grid>
                <Grid item 
                  xs={12}
                  sm={3}
                  md={3}
                  lg={3}
                  xl={3}
                 className={'self-center'}>
                  <FormControl fullWidth>
                    <Button
                      sx={{
                        height: '0',
                        padding: "10px 19px",
                        color: 'white',
                        marginTop: '20px',
                        marginBottom: '15px',
                        fontSize: '13px',
                        borderRadius: '10px',
                        fontWeight: '600',
                        fontFamily: 'customfont',
                        letterSpacing: '1px'
                      }}
                      style={{
                        background: 'rgb(19, 60, 129)',}}
                      onClick={() => {
                        SendEmail();
                      }}
                      endIcon={enableSend === true ? <CircularProgress className={'h-6 w-6'} /> : <SendIcon />}
                      disabled={enableSend}
                    >
                      Send
                    </Button>
                  </FormControl>
                </Grid>
            <div className={'w-full h-[40vh] mt-2 px-0 sm:px-10'}>
              <DataGrid
                sx={{ border: 'none', fontFamily: 'customfont' }}
                rows={alarmReportList}
                rowCount={rowCountState}
                loading={isLoading}
                pagination
                page={page}
                pageSize={pageSize}
                paginationMode="server"
                onPageChange={onPageChange}
                onPageSizeChange={onPageSizeChange}
                columns={columns}
                // rowHeight={70}
                getRowHeight={() => 'auto'}
              />
            </div>

        </Grid>
      </form>
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </Grid>
  );
}

export default Alarm;
