import React, { useState, useEffect } from 'react';
import {
  FormControl, TextField, Button, Grid, Typography, CircularProgress,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import DownloadIcon from '@mui/icons-material/Download';
import SendIcon from '@mui/icons-material/Send';
import { FetchServerUsageReportDetails } from '../../services/LoginPageService';
import { EmailServerUtilizationReportService, serverUtiliExport } from '../../services/DownloadCsvReportsService';
import NotificationBar from '../notification/ServiceNotificationBar';
import { currentDateValidator, dateRangevalidator } from '../../utils/helperFunctions';

const columns = [
  {
    field: 'date',
    headerName: 'Date',
    minWidth: 100,
    maxWidth: 150,
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
    minWidth: 100,
    maxWidth: 150,
    flex: 1,
    align: 'center',
    headerAlign: 'center',
  },
  {
    field: 'perc_memory_usage',
    headerName: 'Physical Memory ( Avg RAM % )',
    minWidth: 310,
    flex: 1,
    align: 'center',
    headerAlign: 'center',
  },
  {
    field: 'disk_usage',
    headerName: 'Disk Usage',
    minWidth: 100,
    flex: 1,
    align: 'center',
    headerAlign: 'center',
  },
  {
    field: 'avg_cpu_load',
    headerName: 'AVG CPU %',
    minWidth: 200,
    align: 'center',
    flex: 1,
    headerAlign: 'center',
  },
];

const dateFormat = (value) => {
  const date = value.split('-');
  const dateValue = `${date[2]}-${date[1]}-${date[0]}`;
  return dateValue;
};

function ServerUtilization() {
  const [isLoading, setGridLoading] = useState(false);
  const [serverUsageReportList, setServerUsageReportList] = useState([]);
  const [unTaggedServerUsageReportList, setUnTaggedServerUsageReportList] = useState();
  const [page, setPage] = useState(0);
  const [pageSize, setPageSize] = useState(10);
  const [rowCountState, setRowCountState] = useState(0);
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');
  const [enableSend, setEnableSend] = useState(false);
  const [enableDownload, setEnableDownload] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    fetchNewData();
  }, [unTaggedServerUsageReportList, page]);

  const handleSubmit = (e) => {
    e.preventDefault();
    fromDate > toDate ? dateRangevalidator(setNotification) : fetchNewData();
  };

  const fetchNewData = () => {
    if (fromDate !== '' && toDate !== '') {
      setGridLoading(true);
      FetchServerUsageReportDetails({
        page, pageSize, fromDate, toDate,
      }, ServerUsageReportHandleSuccess, ServerUsageReportHandleException);
    }
  };

  const ServerUsageReportHandleSuccess = (dataObject) => {
    setServerUsageReportList(dataObject.data.data);
    setRowCountState(dataObject.data.totalRowCount);
    setGridLoading(false);
  };

  const ServerUsageReportHandleException = (dataObject) => { };

  const handleCancel = () => {
    setDeviceId('');
    setGridLoading(false);
    setUnTaggedServerUsageReportList(!unTaggedServerUsageReportList);
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
      serverUtiliExport({ fromDate, toDate }, serverUtiliExportSuccess, serverUtiliExportException));
    } else {
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select a date range',
      });
    }
  };

  const serverUtiliExportSuccess = (dataObject) => {
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message || 'Success',
      });
    }, 2000);
  };

  const serverUtiliExportException = (errorObject, errorMessage) => {
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
    if (fromDate !== '' && toDate !== '') {
      fromDate > toDate ? dateRangevalidator(setNotification) :
      (setEnableSend(true),
      EmailServerUtilizationReportService({ fromDate, toDate }, handleEmailSuccess, handleEmailException));
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

  return (
    <Grid item>
      <form onSubmit={handleSubmit}>
        <Grid container spacing={2}>
              <Grid item
                xs={12}
                sm={6}
                md={6}
                lg={6}
                xl={6}
               >
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
                sm={6}
                md={6}
                lg={6}
                xl={6}
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
                  <FormControl
                    fullWidth
                    style={{
                      overflow: 'hidden',
                      width: '100%',
                    }}
                  >
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
              rows={serverUsageReportList}
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

export default ServerUtilization;
