import React, { useState, useEffect } from 'react';
import {
  InputLabel, MenuItem, FormControl, Select, TextField, Button, Typography, Grid, CircularProgress, Chip,
} from '@mui/material';
import DownloadIcon from '@mui/icons-material/Download';
import SendIcon from '@mui/icons-material/Send';
import {
  DataGrid,
} from '@mui/x-data-grid';
import { Cancel, CheckCircle, Error } from '@mui/icons-material';
import { EmailcalibrationReportService, EmaileventLogMailService, FetCalibrationReport, FetShowEventLogReport, FetchBumpTestReportDetails } from '../../services/LoginPageService';
import { DownloadCalibrationReportService, DownloadEventLogExportService, DownloadReportBumpTestCsv, EmailBumptestReportService } from '../../services/DownloadCsvReportsService';
import NotificationBar from '../notification/ServiceNotificationBar';
import { currentDateValidator, dateRangevalidator } from '../../utils/helperFunctions';


const EVENT_LOG = ({ deviceList, siteId }) => {
    const [fromDate, setFromDate] = useState('');
    const [toDate, setToDate] = useState('');
    const [device_id, setDeviceId] = useState('');
    const [eventName,setEventName] = useState('');
    const [isLoading, setGridLoading] = useState(false);
    const [calibrationReportList, setCalibrationReportList] = useState([]);
    const [unTaggedBumpTestReportList, setUnTaggedBumpTestReportList] = useState();
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
        FetchNewData();
      }, [unTaggedBumpTestReportList, page]);
    
      const columns = [
        // {
        //   field: 'created_at',
        //   headerName: 'Device Tag',
        //   minWidth: 100,
        //   maxWidth: 150,
        //   flex: 1,
        //   align: 'center',
        //   headerAlign: 'center',
        //   renderCell: (params) => (
        //     <Typography>
        //       {
        //         dateFormat(params.value)
        //       }
        //     </Typography>
        //   ),
        // },
        {
          field: 'date',
          headerName: 'Date',
          minWidth: 50,
          flex: 1,
          align: 'center',
          headerAlign: 'center',
        },
        {
          field: 'time',
          headerName: 'Time',
          minWidth: 50,
          flex: 1,
          align: 'center',
          headerAlign: 'center',
        },
        {
          field: 'user',
          headerName: 'User',
          minWidth: 150,
          flex: 1,
          align: 'center',
          headerAlign: 'center',
        },
        {
          field: 'eventName',
          headerName: 'Event Name',
          minWidth: 150,
          flex: 1,
          align: 'center',
          headerAlign: 'center',
        },
        {
          field: 'eventDetails',
          headerName: 'Event Details',
          minWidth: 600,
          flex: 1,
          overflow: 'auto',
          align: 'center',
          headerAlign: 'center',
        },
      
      ];
      const dateFormat = (value) => {
        const dateTime = value.split(' ');
        const date = dateTime[0].split('-');
        const dateValue = `${date[2]}-${date[1]}-${date[0]}`;
        return dateValue;
      };

      const onPageSizeChange = (newPageSize) => {
        setPageSize(newPageSize);
        FetchNewData();
      };
    
      const handleSubmit = (e) => {
        e.preventDefault();
        fromDate > toDate ? dateRangevalidator(setNotification) : FetchNewData();
      };
    
      const FetchNewData = () => {
        if (fromDate !== '' && toDate !== '') {
          console.log("siteId",siteId);
          setGridLoading(true);

          FetShowEventLogReport({
            page, pageSize, ...siteId, eventName, fromDate, toDate,
          }, CalibrationReportHandleSuccess, CalibrationReportHandleException);
        }
      };
    
      const CalibrationReportHandleSuccess = (dataObject) => {
        setCalibrationReportList(dataObject?.data);
        setRowCountState(dataObject.data.totalRowCount);
        setGridLoading(false);
      };
    
      const CalibrationReportHandleException = () => { };
    
      const handleCancel = () => {
        setFromDate('');
        setToDate('');
        setDeviceId('');
        setGridLoading(false);
        setUnTaggedBumpTestReportList(!unTaggedBumpTestReportList);
      };
    
      const OnPageChange = (newPage) => {
        setPage(newPage);
      };
    
      const DownloadCsv = () => {
        if (fromDate !== '' && toDate !== '') {
          fromDate > toDate ? dateRangevalidator(setNotification) :
          (setEnableDownload(true),
          DownloadEventLogExportService({
            ...siteId, eventName, fromDate, toDate,
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
    
      const SendEmail = () => {
        if (fromDate !== '' && toDate !== '') {
          fromDate > toDate ? dateRangevalidator(setNotification) :
          (setEnableSend(true),
          EmaileventLogMailService({
            ...siteId, eventName, fromDate, toDate,
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
            message: errorMessage || 'Something went wrong',
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

  
        const getRowClassName = (params) => {
          return 'custom-row'; // Apply custom class to each row
        };
      
  return (
    <Grid item>
    <form onSubmit={handleSubmit}>
      <Grid container spacing={1}>
        <Grid
          item
          xs={12}
          sm={4}
          md={4}
          lg={4}
          xl={4}
        >
          <TextField
            fullWidth
            label="From Date"
            type="date"
            variant="standard"
            value={fromDate}
            required
            onChange={(e) => {
              setFromDate(e.target.value);
            }}
            autoComplete="off"
            InputLabelProps={{
              shrink: true,
            }}
            inputProps={{
              max: currentDateValidator()
            }}
          />
        </Grid>
        <Grid
          item
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
            }}
            inputProps={{
              max: currentDateValidator()
            }}
          />
        </Grid>
        <Grid
          item
          xs={12}
          sm={4}
          md={4}
          lg={4}
          xl={4}
        >
          <FormControl fullWidth>
            <InputLabel>Event Names</InputLabel>
            <Select
              value={eventName}
              label="Event Names"
              variant="standard"
              onChange={(e) => {
                setEventName(e.target.value);
              }}
            >
              <MenuItem value="" key={0}>
                <em style={{ fontWeight: 'bold' }}>All</em>
              </MenuItem>
              <MenuItem value='Device Config'>Device Config</MenuItem>
              <MenuItem value='Sensor Config'>Sensor Config</MenuItem>
              <MenuItem value='Firmware Upgrade'>Firmware Upgrade</MenuItem>
              <MenuItem value='Configuration'>Configuration</MenuItem>
              <MenuItem value='Enable / Disable Mode'>Enable / Disable Mode</MenuItem>
              <MenuItem value='Alarm Clearance'>Alarm Clearance</MenuItem>
              <MenuItem value='New user'>New user</MenuItem>
              <MenuItem value='Email Config'>Email Config</MenuItem>
              <MenuItem value='Polling Interval'>Polling Interval</MenuItem>
              <MenuItem value='Location Details'>Location Details</MenuItem>           
            </Select>
          </FormControl>
        </Grid>
        <Grid
          item
          xs={12}
          sm={3}
          md={3}
          lg={3}
          xl={3}
          style={{
            alignSelf: 'center',
          }}
        >
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
        <Grid
          item
          xs={12}
          sm={3}
          md={3}
          lg={3}
          xl={3}
          style={{
            alignSelf: 'center',
          }}
        >
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
        <Grid
          item
          xs={12}
          sm={3}
          md={3}
          lg={3}
          xl={3}
          style={{
            alignSelf: 'center',
          }}
        >
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
              endIcon={enableDownload === true ? <CircularProgress style={{ height: '25px', width: '25px' }} /> : <DownloadIcon />}
              onClick={() => {
                DownloadCsv();
              }}
              disabled={enableDownload}
            >
              Download
            </Button>
          </FormControl>
        </Grid>
        <Grid
          item
          xs={12}
          sm={3}
          md={3}
          lg={3}
          xl={3}
          style={{
            alignSelf: 'center',
          }}
        >
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
              endIcon={enableSend === true ? <CircularProgress style={{ height: '25px', width: '25px' }} /> : <SendIcon />}
              disabled={enableSend}
              onClick={SendEmail}
            >
              Send
            </Button>
          </FormControl>
        </Grid>
        <div className={'w-full mt-3 h-[40vh]  px-0 sm:px-10'}>
          <DataGrid
          sx={{ border: 'none', fontFamily: 'customfont' }}
            rows={calibrationReportList}
            
            loading={isLoading}
            rowsPerPageOptions={[1, 10, 100]}
            pagination
            columns={columns}
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
  )
}

export default EVENT_LOG