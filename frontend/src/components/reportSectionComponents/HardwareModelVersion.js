import { Download, Send } from '@mui/icons-material';
import {
  Button, CircularProgress, FormControl, Grid, InputLabel, MenuItem, Select, Typography,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import React, { useState } from 'react';
import { DownloadHardwareVersionReport, EmailHardwareVersionReportService } from '../../services/DownloadCsvReportsService';
import { FetchHardwareVersionReportDetails } from '../../services/LoginPageService';
import NotificationBar from '../notification/ServiceNotificationBar';

// Date
const convertDate = (value) => {
  let date = '';
  const dateTimeSplit = value && value.split(' ');
  if (dateTimeSplit) {
    const dateSplit = dateTimeSplit[0].split('-');
    date = `${dateSplit[2]}-${dateSplit[1]}-${dateSplit[0]}`;
  }
  return date;
};

// Time
const convertTime = (value) => {
  let time = '';
  const dateTimeSplit = value && value.split(' ');
  if (dateTimeSplit) {
    time = dateTimeSplit[1];
  }
  return time;
};

const columns = [
  {
    field: 'date',
    headerName: 'Date',
    headerAlign: 'center',
    minWidth: 100,
    maxWidth: 200,
    flex: 1,
    align: 'center',
    // renderCell: (params) => (
    //   <Typography>
    //     {
    //       convertDate(params.value)
    //     }
    //   </Typography>
    // ),
  },
  {
    field: 'time',
    headerName: 'Time',
    headerAlign: 'center',
    minWidth: 100,
    maxWidth: 200,
    flex: 1,
    align: 'center',
    // renderCell: (params) => (
    //   <Typography>
    //     {
    //       convertTime(params.value)
    //     }
    //   </Typography>
    // ),
  },
  {
    field: 'stateName',
    headerName: 'Location',
    headerAlign: 'center',
    minWidth: 200,
    flex: 1,
    align: 'center',
  },
  {
    field: 'branchName',
    headerName: 'Branch',
    headerAlign: 'center',
    minWidth: 200,
    flex: 1,
    align: 'center',
  },
  {
    field: 'facilityName',
    headerName: 'Facility',
    headerAlign: 'center',
    minWidth: 200,
    flex: 1,
    align: 'center',
  },
  {
    field: 'buildingName',
    headerName: 'Building',
    headerAlign: 'center',
    minWidth: 200,
    flex: 1,
    align: 'center',
  },
  {
    field: 'floorName',
    headerName: 'Floor',
    headerAlign: 'center',
    minWidth: 200,
    flex: 1,
    align: 'center',
  },
  {
    field: 'labDepName',
    headerName: 'Zone',
    headerAlign: 'center',
    minWidth: 200,
    flex: 1,
    align: 'center',
  },
  {
    field: 'deviceName',
    headerName: 'Device Name',
    headerAlign: 'center',
    minWidth: 300,
    flex: 1,
    align: 'center',
  },
  {
    field: 'deviceModel',
    headerName: 'Device Model No.',
    headerAlign: 'center',
    minWidth: 140,
    maxWidth: 200,
    flex: 1,
    align: 'center',
  },
];

function HardwareModelVersion({ deviceList, siteId }) {
  const [deviceId, setDeviceId] = useState('');
  const [isLoading, setGridLoading] = useState(false);
  const [hardwareVersionreportList, setHardwareVersionreportList] = useState([]);
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

  const HandleDeviceChange = (deviceId) => {
    setDeviceId(deviceId);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    fetchNewData();
  };

  const fetchNewData = () => {
    // if(deviceId !== ''){
    setGridLoading(true);
    FetchHardwareVersionReportDetails({
      page, pageSize, deviceId, ...siteId,
    }, HardwareVersionReportHandleSuccess, HardwareVersionHandleException);
    // }
  };

  const HardwareVersionReportHandleSuccess = (dataObject) => {
    setHardwareVersionreportList(dataObject.data || []);
    setRowCountState(dataObject.totalRowCount || 0);
    setGridLoading(false);
  };

  const HardwareVersionHandleException = (dataObject) => { };

  const handleCancel = () => {
    setDeviceId('');
    setGridLoading(false);
  };

  const onPageChange = (newPage) => {
    setPage(newPage);
    fetchNewData();
  };

  const onPageSizeChange = (newPageSize) => {
    setPageSize(newPageSize);
    fetchNewData();
  };

  const DownloadCsv = () => {
    // if (deviceId !== '') {
    setEnableDownload(true);
    DownloadHardwareVersionReport({ ...siteId, deviceId }, hardwareVersionExportSuccess, hardwareVersionExportException);
    // } else {
    //   setNotification({
    //     status: true,
    //     type: 'error',
    //     message: 'Please select a Device',
    //   });
    // }
  };

  const hardwareVersionExportSuccess = (dataObject) => {
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message || 'Success',
      });
    }, 2000);
  };

  const hardwareVersionExportException = (errorObject, errorMessage) => {
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'error',
        message: errorMessage || 'Something went wrong',
      });
    }, 2000);
  };

  const SendEmail = () => {
    // if (deviceId !== '') {
    setEnableSend(true);
    EmailHardwareVersionReportService({ ...siteId, deviceId }, handleEmailSuccess, handleEmailException);
    // } else {
    //   setNotification({
    //     status: true,
    //     type: 'error',
    //     message: 'Please select a Device',
    //   });
    // }
  };

  const handleEmailSuccess = (dataObject) => {
    setTimeout(() => {
      setEnableSend(false);
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message || ' Success',
      });
    }, 2000);
  };

  const handleEmailException = (errorObject, errorMessage) => {
    setTimeout(() => {
      setEnableSend(false);
      setNotification({
        status: true,
        type: 'error',
        message: errorMessage || ' Something went wrong',
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
      <Grid container spacing={1}>

            <Grid item
            xs={12} sm={12} md={12} lg={12} xl={12}>
              <FormControl fullWidth>
                <InputLabel sx={{ fontFamily: 'customfont', color: 'black' }}>Device</InputLabel>
                <Select
                  value={deviceId}
                  label="Device"
                  variant="outlined"
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
                      width: '100%',
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
                    type="submit">
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
                      DownloadCsv();
                    }}
                    endIcon={enableDownload === true ? <CircularProgress className={'h-6 w-6'} /> : <Download />}
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
                    endIcon={enableSend === true ? <CircularProgress className={'h-6 w-6'} /> : <Send />}
                    disabled={enableSend}
                  >
                    Send
                  </Button>
                </FormControl>
              </Grid>
        <div className={'w-full mt-1 h-[40vh]  px-0 sm:px-10'}>
          <DataGrid
            sx={{ fontFamily: 'customfont', border: 'none' }}
            rows={hardwareVersionreportList}
            rowCount={rowCountState}
            loading={isLoading}
            // rowsPerPageOptions={[5, 10, 100]}
            pagination
            page={page}
            pageSize={pageSize}
            paginationMode="server"
            onPageChange={onPageChange}
            onPageSizeChange={onPageSizeChange}
            columns={columns}
          />
        </div>
      </Grid>
    </form >
    <NotificationBar
      handleClose={handleClose}
      notificationContent={openNotification.message}
      openNotification={openNotification.status}
      type={openNotification.type}
    />
  </Grid >
  );
}

export default HardwareModelVersion;
