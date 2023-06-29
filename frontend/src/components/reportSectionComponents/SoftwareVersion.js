import React, { useState, useEffect } from 'react';
import {
  FormControl, Button, Typography, Grid, CircularProgress,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import DownloadIcon from '@mui/icons-material/Download';
import SendIcon from '@mui/icons-material/Send';
import { FetchApplicationVersionReportDetails } from '../../services/LoginPageService';
import { DownloadAppVersionReport, EmailAppVersionReportService } from '../../services/DownloadCsvReportsService';
import NotificationBar from '../notification/ServiceNotificationBar';

const columns = [
  {
    field: 'versionNumber',
    headerName: 'Version',
    minWidth: 100,
    maxWidth: 200,
    flex: 1,
    align: 'center',
    headerAlign: 'center',
  },
  {
    field: 'summary',
    headerName: 'Summary',
    minWidth: 400,
    flex: 1,
    align: 'center',
    headerAlign: 'center',
  },
  {
    field: 'created_at',
    headerName: 'Date',
    headerAlign: 'center',
    flex: 1,
    align: 'center',
    minWidth: 100,
    maxWidth: 200,
    renderCell: (params) => (
      <Typography>
        {
          convertDate(params.value)
        }
      </Typography>
    ),
  },
  {
    field: 'updated_at',
    headerName: 'Time',
    headerAlign: 'center',
    flex: 1,
    align: 'center',
    minWidth: 100,
    maxWidth: 200,
    renderCell: (params) => (
      <Typography>
        {
          convertTime(params.value)
        }
      </Typography>
    ),
  },
];

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

function SoftwareVersion() {
  const [isLoading, setGridLoading] = useState(false);
  const [AppVersionReportList, setAppVersionReportList] = useState([]);
  const [unTaggedAppVersionReportReportList, setUnTaggedAppVersionReportReportList] = useState();
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
    FetchApplicationVersionReportDetails({ page, pageSize }, AppVersionReportHandleSuccess, AppVersionHandleException);
  }, [unTaggedAppVersionReportReportList]);

  const handleSubmit = (e) => {
    e.preventDefault();
    fetchNewData();
  };

  const fetchNewData = () => {
    setGridLoading(true);
    FetchApplicationVersionReportDetails({
      // fromDate, toDate,
      page, pageSize,
    }, AppVersionReportHandleSuccess, AppVersionHandleException);
  };

  const AppVersionReportHandleSuccess = (dataObject) => {
    setAppVersionReportList(dataObject.data);
    setRowCountState(dataObject.totalRowCount);
    setGridLoading(false);
  };

  const AppVersionHandleException = () => { };

  const handleCancel = () => {
    setAppVersionReportList([]);
    setGridLoading(false);
    setUnTaggedAppVersionReportReportList(!unTaggedAppVersionReportReportList);
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
    setEnableDownload(true);
    DownloadAppVersionReport(appVersionExportSuccess, appVersionExportException);
  };

  const appVersionExportSuccess = (dataObject) => {
    setTimeout(() => {
      setEnableDownload(false);
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message || 'Success',
      });
    }, 2000);
  };

  const appVersionExportException = (errorObject, errorMessage) => {
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
    setEnableSend(true);
    // Email API
    EmailAppVersionReportService(handleEmailSuccess, handleEmailException);
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
        <Grid container spacing={1}>
          <Grid container spacing={1} >
              <Grid item
                xs={12}
                sm={3}
                md={3}
                lg={3}
                xl={3} >
                <FormControl fullWidth >
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
                      letterSpacing: '1px',
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
                xl={3}>
                <FormControl fullWidth>
                  <Button
                    sx={{
                      height: '40px',
                      padding: "10px 39px",
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

          </Grid>
          <div className={'w-full h-[40vh] mt-1 px-0 sm:px-10'}>
            <DataGrid
              sx={{ border: 'none', fontFamily: 'customfont' }}
              rows={AppVersionReportList}
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

export default SoftwareVersion;
