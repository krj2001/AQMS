import React, { useState, useEffect } from 'react';
import {
  Box, InputLabel, MenuItem, FormControl, Select, TextField, Button, Typography, Grid, CircularProgress,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import DownloadIcon from '@mui/icons-material/Download';
import SendIcon from '@mui/icons-material/Send';
import { FetchSensorLogReportDetails } from '../../services/LoginPageService';
import { DownloadReportSensorLogCsv, EmailDeviceLogReportService } from '../../services/DownloadCsvReportsService';
import NotificationBar from '../notification/ServiceNotificationBar';
import { currentDateValidator, dateRangevalidator } from '../../utils/helperFunctions';

function DeviceLogs({ deviceList, siteId }) {
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');
  const [deviceId, setDeviceId] = useState('');
  const [isLoading, setGridLoading] = useState(false);
  const [sensorLogReportList, setSensorLogReportList] = useState([]);
  const [unTaggedSensorLogReportList, setUnTaggedSensorLogReportList] = useState();
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
  }, [unTaggedSensorLogReportList, page]);

  const columns = [
    {
      field: 'created_at',
      headerName: 'Date',
      minWidth: 100,
      maxWidth: 130,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      renderCell: (params) => (
        <Typography>
          {
            dateFormat(params.value)
          }
        </Typography>
      ),
    },
    {
      field: 'time',
      headerName: 'Time',
      minWidth: 80,
      maxWidth: 120,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
      renderCell: (params) => (
        <Typography>
          {
            timeFormat(params.row.created_at)
          }
        </Typography>
      ),
    },
    {
      field: 'email',
      headerName: 'User',
      minWidth: 250,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
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
      headerName: 'Device',
      minWidth: 170,
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
      field: 'criticalMinValue',
      headerName: 'Critical Min Value',
      minWidth: 220,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'criticalMaxValue',
      headerName: 'Critical Max Value',
      minWidth: 220,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'warningMinValue',
      headerName: 'Warning Min Value',
      minWidth: 220,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'warningMaxValue',
      headerName: 'Warning Max Value',
      minWidth: 220,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'outofrangeMinValue',
      headerName: 'Out Of Range Min Value',
      minWidth: 220,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
    {
      field: 'outofrangeMaxValue',
      headerName: 'Out Of Range Max Value',
      minWidth: 220,
      align: 'center',
      flex: 1,
      headerAlign: 'center',
    },
  ];

  const dateFormat = (value) => {
    const dateTime = value.split(' ');
    const date = dateTime[0].split('-');
    const dateValue = `${date[2]}-${date[1]}-${date[0]}`;
    return dateValue;
  };

  const timeFormat = (value) => {
    const dateTime = value.split(' ');
    const dateValue = dateTime[1];
    return dateValue;
  };

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
      fromDate > toDate ? (dateRangevalidator(setNotification)) :
      (setEnableDownload(true),
      DownloadReportSensorLogCsv({
        ...siteId, deviceId, fromDate, toDate,
      }, csvReportHandleSuccess, csvReportHandleException))
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

  const SendEmail = () => {
    if (fromDate !== '' && toDate !== '') {
      fromDate > toDate ? (dateRangevalidator(setNotification)) :
      (setEnableSend(true),
      EmailDeviceLogReportService({
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
        message: dataObject.message || 'Success',
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

  const fetchNewData = () => {
    if (fromDate !== '' && toDate !== '') {
      setGridLoading(true);
      setSensorLogReportList([]);
      FetchSensorLogReportDetails({
        page, pageSize, ...siteId, deviceId, fromDate, toDate,
      }, SensorLogReportHandleSuccess, SensorLogHandleException);
    }
  };

  const SensorLogReportHandleSuccess = (dataObject) => {
    setSensorLogReportList(dataObject.data.data || []);
    setRowCountState(dataObject.data.totalRowCount);
    setGridLoading(false);
  };

  const SensorLogHandleException = () => { };

  const handleCancel = () => {
    setFromDate('');
    setToDate('');
    setDeviceId('');
    setGridLoading(false);
    setUnTaggedSensorLogReportList(!unTaggedSensorLogReportList);
  };

  const onPageChange = (newPage) => {
    setPage(newPage);
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
      <form onSubmit={handleSubmit} className='w-full'>
        <Grid container spacing={2}>
              <Grid item                   
                  xs={12}
                  sm={4}
                  md={4}
                  lg={4}
                  xl={4}>
                <TextField
                  sx={{ width: '100%' }}
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
                  label="To date"
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
                    <Button type="submit"
                      sx={{
                        height: '40px',
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
                      >
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
                        height: '40px',
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
                      onClick={handleCancel}>
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
                        height: '40px',
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
                        height: '40px',
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
                      onClick={SendEmail}
                      endIcon={enableSend === true ? <CircularProgress className={'h-6 w-6'} /> : <SendIcon />}
                      disabled={enableSend}
                    >
                      Send
                    </Button>
                  </FormControl>
                </Grid>
          <Box className={'w-full h-[40vh] mt-2 px-0 sm:px-10'}>
            <DataGrid
              sx={{ border: 'none', fontFamily: 'customfont' }}
              rows={sensorLogReportList}
              rowCount={rowCountState}
              loading={isLoading}
              pagination
              page={page}
              pageSize={pageSize}
              paginationMode="server"
              onPageChange={onPageChange}
              onPageSizeChange={onPageSizeChange}
              columns={columns}
            />
          </Box>
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

export default DeviceLogs;
