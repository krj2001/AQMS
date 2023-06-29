import React, { useState, useEffect } from 'react';
import {
  Box, InputLabel, MenuItem, FormControl, Select, TextField, Stack, Button, Grid, LinearProgress, CircularProgress,
} from '@mui/material';
import DownloadIcon from '@mui/icons-material/Download';
import SendIcon from '@mui/icons-material/Send';

import { FetchSensorStatusReportDetails } from '../../services/LoginPageService';
import { DownloadReportAqiCsv, DownloadSensorStatusCsv, EmailSensorStatusReportService } from '../../services/DownloadCsvReportsService';
import NotificationBar from '../notification/ServiceNotificationBar';
import { currentDateValidator, dateRangevalidator } from '../../utils/helperFunctions';
/* eslint-disable no-shadow */
function SensorStatus({ deviceList, siteId }) {
  const [deviceId, setDeviceId] = useState('');
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');
  const [gasCollection, setGasCollection] = useState([]);
  const [sensorDate, setSensorDate] = useState([]);
  const [sensorDateValues, setSensorDateValues] = useState([]);
  const [rawDataSet, setRawDataSet] = useState({});
  const [tableHeaderList, setTableHeaderList] = useState([]);
  const [tableDateList, setTableDateList] = useState([]);
  const [tableLoader, setTableLoader] = useState(false);
  const [enableSend, setEnableSend] = useState(false);
  const [enableDownload, setEnableDownload] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const valueSet = {
    headerItem: {
      gasCollection: [
        'Test Sen',
        'modbus tester',
        'PM10',
        'PM2.5',
        'Test Sen',
        'modbus tester',
        'PM10',
        'PM2.5',
        'Test Sen',
        'modbus tester',
      ],
    },
    data: {
      '2022-10-01': {
        min: [
          '1.1',
          '1.2',
          '1.3',
          '1.4',
          '1.1',
          '1.2',
          '1.3',
          '1.4',
          '1.1',
          '1.2',
        ],
        max: [
          '1.9',
          '1.8',
          '1.7',
          '1.6',
          '1.9',
          '1.8',
          '1.7',
          '1.6',
          '1.9',
          '1.8',
        ],
        avg: [
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
        ],
        status: [{
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        ],
      },
      '2022-10-02': {
        min: [
          '2.1',
          '2.0',
          '2.3',
          '2.1',
          '2.0',
          '2.3',
          '2.1',
          '2.0',
          '2.0',
          '2.3',
        ],
        max: [
          '2.8',
          '2.9',
          '2.7',
          '2.8',
          '2.9',
          '2.7',
          '2.8',
          '2.9',
          '2.8',
          '2.9',
        ],
        avg: [
          '2.5',
          '2.5',
          '2.5',
          '2.5',
          '2.5',
          '2.5',
          '2.5',
          '2.5',
          '2.5',
          '2.5',
        ],
        status: [{
          type: 'Good',
          color: 'green',
        },
        {
          type: 'Stel',
          color: 'red',
        },
        {
          type: 'Out of range',
          color: 'purple',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'Stel',
          color: 'red',
        },
        {
          type: 'Out of range',
          color: 'purple',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'Stel',
          color: 'red',
        },
        {
          type: 'Stel',
          color: 'red',
        },
        {
          type: 'Out of range',
          color: 'purple',
        },
        ],
      },
      '2022-10-03': {
        min: [
          '3.1',
          '3.2',
          '3.3',
          '3.1',
          '3.2',
          '3.3',
          '3.1',
          '3.2',
          '3.1',
          '3.2',
        ],
        max: [
          '3.9',
          '3.8',
          '3.7',
          '3.9',
          '3.8',
          '3.7',
          '3.9',
          '3.8',
          '3.9',
          '3.8',
        ],
        avg: [
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
        ],
        status: [{
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        ],
      },
      '2022-10-04': {
        min: [
          '1.1',
          '1.2',
          '1.3',
          '1.4',
          '1.1',
          '1.2',
          '1.3',
          '1.4',
          '1.4',
          '1.1',
        ],
        max: [
          '1.9',
          '1.8',
          '1.7',
          '1.6',
          '1.9',
          '1.8',
          '1.7',
          '1.6',
          '1.9',
          '1.8',
        ],
        avg: [
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
        ],
        status: [{
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        ],
      },
      '2022-10-05': {
        min: [
          '3.1',
          '3.2',
          '3.3',
          '3.1',
          '3.2',
          '3.3',
          '3.1',
          '3.2',
          '3.1',
          '3.2',
        ],
        max: [
          '3.9',
          '3.8',
          '3.7',
          '3.9',
          '3.8',
          '3.7',
          '3.9',
          '3.8',
          '3.9',
          '3.8',
        ],
        avg: [
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
          '3.5',
        ],
        status: [{
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        {
          type: 'TWA',
          color: 'gold',
        },
        {
          type: 'Good',
          color: 'green',
        },
        ],
      },
      '2022-10-06': {
        min: [
          '1.1',
          '1.2',
          '1.3',
          '1.4',
          '1.1',
          '1.2',
          '1.3',
          '1.4',
          '1.4',
          '1.1',
        ],
        max: [
          '1.9',
          '1.8',
          '1.7',
          '1.6',
          '1.9',
          '1.8',
          '1.7',
          '1.6',
          '1.9',
          '1.8',
        ],
        avg: [
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
          '1.5',
        ],
        status: [{
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        {
          type: 'Critical',
          color: 'red',
        },
        {
          type: 'Warning',
          color: 'gold',
        },
        {
          type: 'Out Of Range',
          color: 'purple',
        },
        ],
      },
    },
  };
  const tableHeaderLists = valueSet.headerItem.gasCollection;
  const keys = Object.keys(valueSet.data || {});
  const dateList = keys;

  // useEffect(() => {
  //     fetchSensorService();
  // }, []);

  const fetchSensorService = () => {
    if (fromDate !== '' && toDate !== '') {
      setTableLoader(true);
      FetchSensorStatusReportDetails({
        ...siteId, deviceId, fromDate, toDate,
      }, SensorStatusReportHandleSuccess, SensorStatusReportHandleException);
    } else {
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select a date range',
      });
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    fromDate > toDate ? dateRangevalidator(setNotification) : fetchSensorService();
  };

  const SensorStatusReportHandleSuccess = (dataObject) => {
    setRawDataSet(dataObject || {});
    setTableHeaderList(dataObject?.headerItem?.gasCollection || []);
    setTableDateList(Object.keys(dataObject?.data || []));
    setTableLoader(false);

    // console.log(dataObject.headerItem);
    // setGasCollection(dataObject.headerItem || []);
    // const keys = Object.keys(dataObject.data || {});
    // setSensorDate(keys || []);
    // setSensorDateValues(dataObject.data || []);
    // console.log(keys);

    // setSensorDateValues(dataObject.data);
    // console.log(dataObject.data);
  };

  const SensorStatusReportHandleException = () => { };

  const HandleDeviceChange = (deviceId) => {
    setDeviceId(deviceId);
  };

  const handleCancel = () => {
    setFromDate('');
    setToDate('');
    setDeviceId('');
    setTableLoader(false);
  };

  const DownloadCsv = () => {
    if (fromDate !== '' && toDate !== '') {
      fromDate > toDate ? dateRangevalidator(setNotification) :
      (setEnableDownload(true),
      DownloadSensorStatusCsv({
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

  const csvReportHandleSuccess = (dataObject) => {
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message || 'Success',
      });
    }, 3000);
  };

  const csvReportHandleException = (errorObject, errorMessage) => { 
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'error',
        message: errorMessage || 'Something went wrong',
      });
    }, 3000);
  };

  const dateFormat = (value) => {
    const date = value.split('-');
    const dateValue = `${date[2]}-${date[1]}-${date[0]}`;
    return dateValue;
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
      EmailSensorStatusReportService({...siteId, deviceId, fromDate, toDate,}, handleEmailSuccess, handleEmailException));
    } else {
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select a date range',
      });
    }
  };

  const handleEmailSuccess = (dataObject) => {
    setTimeout(()=>{
      setEnableSend(false);
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message || 'Success',
      });
    }, 2000);
  }

  const handleEmailException = (errorObject, errorMessage) => {
    setTimeout(()=>{
      setEnableSend(false);
      setNotification({
        status: true,
        type: 'error',
        message: errorMessage || 'Something went wrong',
      });
    }, 2000);
  }
  return (
    <Grid item>
      <form onSubmit={handleSubmit} className='w-full'>
        <Grid container spacing={2}>
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
                  onChange={(e) => {
                    setFromDate(e.target.value);
                  }}
                  autoComplete="off"
                  InputLabelProps={{
                    shrink: true,
                    style: { fontFamily: 'customfont' }
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
                    shrink: true,
                    style: { fontFamily: 'customfont' }
                  }}
                  inputProps={{
                    max: currentDateValidator(),
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
                      <em style={{ fontWeight: 'bold' }}>All</em>
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
                      autoFocus type="submit">Submit</Button>
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
                      autoFocus onClick={handleCancel}>Cancel</Button>
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
                      autoFocus
                      disabled={enableDownload}
                      endIcon={enableDownload === true ? <CircularProgress className={'h-6 w-6'} /> : <DownloadIcon />}
                      onClick={() => {
                        DownloadCsv();
                      }}
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
                      onClick={sendEmail}
                      disabled={enableSend}
                      endIcon={enableSend === true ? <CircularProgress className={'h-6 w-6'} /> : <SendIcon />}
                    >
                      Send
                    </Button>
                  </FormControl>
                </Grid>
        </Grid>
        <Stack className={'w-full h-auto overflow-auto'}>
          <table className={'w-full mt-7 border-spacing-0 border-collapse border border-solid border-black'}>
            <tr className={'border border-solid border-black'}>
              {tableLoader === false && (
                tableHeaderList.length > 0 && (
                  <th className={'border border-solid border-black whitespace-nowrap'} colSpan={2}>Date</th>
                )
              )}
              {tableLoader === false && (
                tableHeaderList?.map((data) => {
                  return (
                    <th className={'border border-solid border-black whitespace-nowrap px-2.5 py-1.5 min-w-[100px]'}>{data}</th>
                  );
                })
              )}
            </tr>
            {tableLoader === false && (tableDateList?.map((data) => {
              return (
                <tbody className={'border border-solid border-black'}>
                  <tr>
                    <th className={'border border-solid border-black whitespace-nowrap px-2.5 py-1.5'} rowSpan="4">{dateFormat(data)}</th>
                    <td className={'border border-solid border-black px-2.5'}>MIN</td>
                    {rawDataSet?.data[data]?.min?.map((data) => {
                      return (<td className={'border border-solid border-black'}>{data}</td>);
                    })}
                  </tr>
                  <tr>
                    <td className={'border border-solid border-black px-2.5'}>MAX</td>
                    {rawDataSet?.data[data]?.max?.map((data) => {
                      return (<td className={'border border-solid border-black px-2.5'}>{data}</td>);
                    })}
                  </tr>
                  <tr>
                    <td className={'border border-solid border-black px-2.5'}>AVG</td>
                    {rawDataSet?.data[data]?.avg?.map((data) => {
                      return (<td className={'border border-solid border-black px-2.5'}>{data}</td>);
                    })}
                  </tr>
                  <tr>
                    <td className={'border border-solid border-black px-2.5'}>Status</td>
                    {rawDataSet?.data[data]?.status?.map((data) => {
                      return (<td className={'border border-solid border-black px-2.5 whitespace-nowrap'} style={{ color: data.alertColor }}>{data.alertType}</td>);
                    })}
                  </tr>
                </tbody>
              );
            })
            )}
          </table>
        </Stack>
      </form>
      {tableLoader === true
        && (
          <Box sx={{ width: '100%', marginTop: '100px' }}>
            <LinearProgress />
          </Box>
        )}
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </Grid>
  );
}

export default SensorStatus;
