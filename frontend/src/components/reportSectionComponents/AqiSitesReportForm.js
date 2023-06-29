import React, { useState, useEffect } from 'react';
import {
  FormControl, Button, Typography, TextField, Grid, InputLabel, Select, MenuItem, CircularProgress,
} from '@mui/material';
import SendIcon from '@mui/icons-material/Send';
import DownloadIcon from '@mui/icons-material/Download';
import { DataGrid } from '@mui/x-data-grid';
import { FetchAqiStatusReportDetails } from '../../services/LoginPageService';
import { DownloadReportAqiCsv, EmailAQIIndexReportService } from '../../services/DownloadCsvReportsService';
import { currentDateValidator, dateRangevalidator, setAQIColor } from '../../utils/helperFunctions';
import AQITrendModal from './AQITrendModal';
import NotificationBar from '../notification/ServiceNotificationBar';
import CircleIcon from '@mui/icons-material/Circle';

function AqiSitesReportForm({ siteId, deviceList }) {
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');
  const [deviceId, setDeviceId] = useState('');
  const [isLoading, setGridLoading] = useState(false);
  const [rowCountState, setRowCountState] = useState(0);
  const [page, setPage] = useState(0);
  const [pageSize, setPageSize] = useState(10);
  const [aqiStatusReportList, setAqiStatusReportList] = useState([]);
  const [unTaggedAqiStatusReportList, setUnTaggedAqiStatusReportList] = useState();
  const [openTrend, setOpenTrend] = useState(false);
  const [enableSend, setEnableSend] = useState(false);
  const [enableDownload, setEnableDownload] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    // FetchAqiStatusReportDetails({}, AqiStatusReportHandleSuccess, AqiStatusReportHandleException);
    fetchNewData();
  }, [unTaggedAqiStatusReportList, page]);

  const AqiStatusReportHandleSuccess = (dataObject) => {
    setAqiStatusReportList(dataObject.data.data || []);
    setRowCountState(dataObject.data.totalRowCount || 0);
    setGridLoading(false);
  };

  const AqiStatusReportHandleException = () => { };

  const dateFormat = (value) => {
    const dateTime = value.split(' ');
    const date = dateTime[0].split('-');
    const dateValue = `${date[2]}-${date[1]}-${date[0]}`;
    return dateValue;
  };

  const columns = [
    {
      field: 'date',
      headerName: 'Date',
      minWidth: 110,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
      renderCell: (params) => (
        <Typography>
          {
            dateFormat(params.value)
          }
        </Typography>
      ),
    },
    {
      field: 'stateName',
      headerName: 'Location',
      minWidth: 120,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
    },
    {
      field: 'branchName',
      headerName: 'Branch',
      minWidth: 120,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
    },
    {
      field: 'facilityName',
      headerName: 'Facilities',
      minWidth: 100,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
    },
    {
      field: 'buildingName',
      headerName: 'Building',
      minWidth: 130,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
    },
    {
      field: 'floorName',
      headerName: 'Floor',
      minWidth: 130,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
    },
    {
      field: 'labDepName',
      headerName: 'Zone',
      minWidth: 100,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
    },
    {
      field: 'deviceName',
      headerName: 'Device Name',
      minWidth: 120,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
    },
    {
      field: 'AqiValue',
      headerName: 'AQI Value',
      minWidth: 50,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
      renderCell: (params) => (
        <span style={{
          color: setAQIColor(parseFloat(params.value)),
        }}
        >
          
          {params.value}
        </span>
      ),
    },
    {
      field: 'AqiStatus',
      headerName: 'AQI Status',
      minWidth: 100,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
      renderCell: (params) => (
        <span style={{
          color: setAQIColor(parseFloat(params.value)),
        }}
        >
          {/* {params.value} */}
          <CircleIcon/>
        </span>
      ),
    },

  ];

  const fetchNewData = () => {
    if (fromDate !== '' && toDate !== '') {
      setGridLoading(true);
      FetchAqiStatusReportDetails({
        page,
        pageSize,
        ...siteId,
        deviceId,
        fromDate,
        toDate,
      }, AqiStatusReportHandleSuccess, AqiStatusReportHandleException);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    fromDate > toDate ? dateRangevalidator(setNotification) : fetchNewData();
  };

  const onPageChange = (newPage) => {
    setPage(newPage);
  };
  const onPageSizeChange = (newPageSize) => {
    setPageSize(newPageSize);
  };

  const DownloadCsv = () => {
    if (fromDate !== '' && toDate !== '') {
      fromDate > toDate ? dateRangevalidator(setNotification) :
      (setEnableDownload(true),
      DownloadReportAqiCsv({
        ...siteId, deviceId, fromDate, toDate,
      }, csvReportHandleSuccess, csvReportHandleException));
    } else {
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select a date range',
      });
    }
    // setReportControlType("download");
  };

  const csvReportHandleSuccess = () => {
    setTimeout(() => {
      setEnableDownload(false);
    }, 3000);
  };

  const csvReportHandleException = () => {
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'error',
        message: 'Something went wrong...',
      });
    }, 3000);
  };

  const handleAQITrend = () => {
    if (fromDate !== '' && toDate !== '' && deviceId !== '') {
      setOpenTrend(true);
    } else {
      // Show Error Notification
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select a date range and a device to view trend graph',
      });
    }
  };

  const HandleDeviceChange = (value) => {
    setDeviceId(value);
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const sendEmail = () => {
    if (fromDate !== '' && toDate !== '') {
      fromDate > toDate ? dateRangevalidator(setNotification) :
      (setEnableSend(true),
      EmailAQIIndexReportService({
        ...siteId, deviceId, fromDate, toDate,
      }, handleEmailSuccess, handlesEmailException));
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

  const handlesEmailException = (errorObject, errorMessage) => {
    setTimeout(() => {
      setEnableSend(false);
      setNotification({
        status: true,
        type: 'error',
        message: 'Something went wrong...',
      });
    }, 2000);
  };
  return (
    <form onSubmit={handleSubmit} className='w-full '>


        <Grid container spacing={1}>
                <Grid item 
                  xs={12}
                  sm={4}
                  md={4}
                  lg={4}
                  xl={4}
                >
                  <TextField
                    sx={{ width: '100%' }}
                    fullWidth
                    label="From Date"
                    type="date"
                    value={fromDate}
                    variant="standard"
                    required
                    onChange={(e) => { setFromDate(e.target.value); }}
                    autoComplete="off"
                    InputLabelProps={{ shrink: true, style: { fontFamily: 'customfont' } }}
                    inputProps={{ max: currentDateValidator() }}
                  />

                </Grid>
                <Grid item 
                  xs={12}
                  sm={4}
                  md={4}
                  lg={4}
                  xl={4}
                >
                  <TextField
                    fullWidth
                    // sx={{ minWidth: 250 }}
                    label="To Date"
                    type="date"
                    value={toDate}
                    variant="standard"
                    required
                    onChange={(e) => { setToDate(e.target.value); }}
                    autoComplete="off"
                    InputLabelProps={{ shrink: true, style: { fontFamily: 'customfont' } }}
                    inputProps={{ max: currentDateValidator() }}
                  />
                </Grid>
                <Grid item 
                  xs={12}
                  sm={4}
                  md={4}
                  lg={4}
                  xl={4}
                >
                  <FormControl fullWidth >
                    <InputLabel sx={{ fontFamily: 'customfont', color: 'black' }}>Devices</InputLabel>

                    <Select

                      value={deviceId}
                      label="Devices"
                      variant="standard"

                      onChange={(e) => { HandleDeviceChange(e.target.value); }}
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
                    fullWidth onClick={handleAQITrend}>
                    Trend
                  </Button>
                </Grid>
             
              {/* <Grid item
            xs={6}
            sm={6}
            md={3}
            lg={3}
            xl={1.2}
            style={{
              alignSelf: 'center'
            }}
          >
            <FormControl fullWidth>
              <Button size="medium" variant="contained"  onClick={handleCancel} >
                Cancel
              </Button>
            </FormControl>
          </Grid> */}
              
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
                        width: '100%',
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
                      onClick={() => { DownloadCsv(); }}
                      style={{
                        background: 'rgb(19, 60, 129)',}}
                      disabled={enableDownload}
                    >
                      {enableDownload === true ? <CircularProgress className={'h-6 w-6 text-white'} /> : <DownloadIcon sx={{ mr: 1 }} />}

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
                    endIcon={enableSend === true ? <CircularProgress className={'h-6 w-6'} /> : <SendIcon />}
                    onClick={sendEmail}
                    fullWidth
                    disabled={enableSend}
                  >
                    Send
                  </Button>
                </Grid>


            <div className='w-full mt-2 mb-2 flex flex-col sm:flex-row gap-5 justify-center'>
              <div style={{fontFamily:'customfont'}}>
              <CircleIcon style={{color:"#58AC4C"}}/> Good
              </div>
              <div style={{fontFamily:'customfont'}}>
              <CircleIcon style={{color:"#A8CC54"}}/> Satisfactory
              </div>
              <div style={{fontFamily:'customfont'}}>
              <CircleIcon style={{color:"#FFFC34"}}/> Moderate
              </div>
              <div style={{fontFamily:'customfont'}}>
              <CircleIcon style={{color:"#F89C34"}}/> Poor
              </div>
              <div style={{fontFamily:'customfont'}}>
              <CircleIcon style={{color:"#F03C34"}}/> Very Poor
              </div>
              <div style={{fontFamily:'customfont'}}>
              <CircleIcon style={{color:"#B02C24"}}/> Severe
              </div>
              </div>
          
        </Grid>
        <div className={'h-[40vh] w-full mt-2'}>
          <DataGrid
            sx={{ border: 'none', fontFamily: 'customfont' }}
            rows={aqiStatusReportList}
            rowCount={rowCountState}
            columns={columns}
            page={page}
            pagination
            loading={isLoading}
            pageSize={pageSize}
            onPageChange={onPageChange}
            onPageSizeChange={onPageSizeChange}
            // rowsPerPageOptions={[5, 10]} 
            paginationMode="server"
          />
        </div>
      
      <AQITrendModal setOpenTrend={setOpenTrend} openTrend={openTrend} id={{ deviceId, fromDate, toDate }} type="device" />
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </form >
  );
}

export default AqiSitesReportForm;
