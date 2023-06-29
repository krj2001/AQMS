import {
  Box, Button, Card, CardContent, CardHeader, Dialog, DialogContent, DialogTitle, Grid, TextField, Typography,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import { DataGrid, gridClasses } from '@mui/x-data-grid';
import { Add, DeleteOutlined, Edit } from '@mui/icons-material';
import NotificationBar from './notification/ServiceNotificationBar';
import { AppVersionAddService, AppVersionDeleteService, AppVersionEditService, AppVersionFetchService } from '../services/LoginPageService';
import DeleteConfirmationDailog from '../utils/confirmDeletion';
/* eslint-disable no-unused-vars */
function AppVersion() {
  const [open, setOpen] = useState(false);
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editData, setEditData] = useState({});
  const [versionNumber, setVersionNumber] = useState('');
  const [summary, setSummary] = useState('');
  const [data, setData] = useState([]);
  const [isLoading, setloading] = useState(true);
  const [refreshData, setRefreshData] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const columns = [
    {
      field: 'versionNumber',
      headerName: 'Version',
      minWidth: 110,
      maxWidth: 200,
      flex: 1,
      headerAlign: 'center',
      align: 'center'
    },
    {
      field: 'summary',
      headerName: 'Summary',
      minWidth: 350,
      flex: 1,
      align:'center',
      headerAlign: 'center',
    },
    {
      field: 'created_at',
      headerName: 'Date',
      minWidth: 130,
      maxWidth: 200,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
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
      align: 'center',
      flex: 1,
      minWidth: 130,
      maxWidth: 200,
      renderCell: (params) => (
        <Typography>
          {
            convertTime(params.value)
          }
        </Typography>
      ),
    },
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      minWidth: 100,
      maxWidth: 200,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      cellClassName: 'actions',
      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];


  useEffect(()=>{
    AppVersionFetchService(fetchHandleSuccess, fetchException);
  },[refreshData]);

  useEffect(()=>{
    setVersionNumber(editData?.versionNumber || '');
    setSummary(editData?.summary || '');
  }, [editData]);

  const convertDate = (value) => {
    var date = '';
    var dateTimeSplit = value && value.split(" ");
    if(dateTimeSplit){
      var dateSplit = dateTimeSplit[0].split("-");
      date = dateSplit[2] + "-" + dateSplit[1] + "-" + dateSplit[0];
    }
    return date;
  }

  const convertTime = (value) => {
    var time = '';
    var dateTimeSplit = value && value.split(" ");
    if(dateTimeSplit){
      // var dateSplit = dateTimeSplit[1].split(".");
      time = dateTimeSplit[1];
    }
    return time;
  }

  function DeleteData(props) {
    return(
      <DeleteOutlined
        onClick={() => {
          // BuildingDeleteService(props.selectedRow, deletehandleSuccess, deletehandleException);
          setDeleteId(props.selectedRow.id);
          setDeleteDailogOpen(true);
        }}
        style={{ cursor: 'pointer' }}
      />
    )
  }

  function EditData(props) {
    return (
      <Edit
        onClick={() => {
          setIsAddButton(false);
          setEditData(props.selectedRow);
          setOpen(true);
        }}
        style={{ cursor: 'pointer' }}
      />
    );
  }

  const deletehandleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
      setDeleteDailogOpen(false);
    }, 3000);
  };

  const deletehandleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setTimeout(() => {
      handleClose();
    }, 3000);
  };

  const fetchHandleSuccess = (dataObject) =>{
    setloading(false);
    setData(dataObject.data || []);
    // Auto Increment feature no more required

    // setVersionNumber(()=>{
    //   if(dataObject.totalRowCount > 0){
    //     let latestVersion = parseFloat(dataObject.data[dataObject.totalRowCount - 1].versionNumber);
    //     return parseFloat(latestVersion + 0.1).toFixed(1);
    //   } else {
    //     return '1.0';
    //   }
    // });
  }

  const fetchException = (errorObject, errorMessage) =>{ };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (isAddButton) {
      AppVersionAddService({versionNumber, summary},appVersionAddHandleSuccess, handleException);
    } else {
      // Edit API
      AppVersionEditService({id:editData?.id, versionNumber, summary}, appVersionAddHandleSuccess, handleException);
    }
  };

  const appVersionAddHandleSuccess = (dataObject) => {
    setRefreshData(oldValue=>!oldValue);
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setSummary('');
    setVersionNumber('');
    setOpen(false);
  };

  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setSummary('');
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  return (
    <>
      <Card className={'m-8'} style={{boxShadow:'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', borderRadius:'12px'}}>
        <CardHeader
          title={
            <Box
              sx={{
                mb: '10px',
                alignItems: 'center',
                display: 'flex',
                justifyContent: 'space-between',
                flexWrap: 'wrap',
                m: 3,
              }}
            >
              <Typography variant="h5" 
              sx={{ ml: 1,
              fontFamily:'customfont', fontWeight:'600', letterSpacing:'1px' }}>
                Application Version
              </Typography>
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
                  setOpen(true);
                }}
              >
                <Add sx={{ mr: 1 }} />
                Add version
              </Button>
            </Box>
          }
        />
        <CardContent>
          <div className={'m-0 min-h-[300px] h-[500px] text-justify'}>
            <DataGrid
              rows={data}
              columns={columns}
              pageSize={5}
              loading={isLoading}
              rowsPerPageOptions={[5]}
              disableSelectionOnClick
              getRowHeight={() => 'auto'}
              style={{border:'none', fontFamily:'customfont'}}
              sx={{
                [`& .${gridClasses.cell}`]: {
                  py: 1,
                },
              }}
            />
          </div>
          <Dialog
            sx={{ '& .MuiDialog-paper': { minWidth: '50%' } }}
            maxWidth="sm"
            open={open}
          >
            <DialogTitle sx={{fontFamily:'customfont', letterSpacing:'1px', fontWeight:'600'}}>
              {isAddButton ? 'Add version' : 'Edit version'}
            </DialogTitle>
            <DialogContent>
              <form onSubmit={handleSubmit}>
                <Grid container>
                  <Grid
                    xs={12}
                    sm={12}
                    md={12}
                    lg={12}
                    xl={12}
                    item
                  >
                    <TextField
                      fullWidth
                      sx={{ mt: 2 }}
                      label="Version Number"
                      type="text"
                      value={versionNumber}
                      variant="outlined"
                      // disabled={true}
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2
                                    border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md
                                    focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onChange={(e) => { setVersionNumber(e.target.value); }}
                      autoComplete="off"
                      InputLabelProps={{
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </Grid>
                  <Grid
                    xs={12}
                    sm={12}
                    md={12}
                    lg={12}
                    xl={12}
                    item
                  >
                    <TextField
                      fullWidth
                      label="Summary"
                      value={summary}
                      onChange={(e)=>{setSummary(e.target.value);}}
                      sx={{ mt: 2 }}
                      type="text"
                      multiline
                      rows={4}
                      placeholder="Version Summary"
                      required
                      autoComplete="off"
                      variant="outlined"
                      InputLabelProps={{
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </Grid>
                </Grid>
                <div className="rounded-md -space-y-px float-right mt-5">
                  <Button
                    type="submit"
                    sx={{
                      height: '40px',
                      padding: "10px 19px",
                      color: 'white',
                      // marginTop: '20px',
                      // marginBottom: '15px',
                      marginRight:'20px',
                      fontSize: '13px',
                      borderRadius: '10px',
                      fontWeight: '600',
                      fontFamily: 'customfont',
                      letterSpacing: '1px'
                    }}
                    style={{
                      background: 'rgb(19, 60, 129)',}}
                  >
                    {isAddButton ? 'Add' : 'Update'}
                  </Button>
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
                      setSummary('');
                      setVersionNumber('');
                      setEditData({});
                      setOpen(false);
                    }}
                  >
                    Cancel
                  </Button>
                </div>
              </form>
            </DialogContent>
          </Dialog>

          <NotificationBar
            handleClose={handleClose}
            notificationContent={openNotification.message}
            openNotification={openNotification.status}
            type={openNotification.type}
          />

          <DeleteConfirmationDailog
            open={deleteDailogOpen}
            setOpen={setDeleteDailogOpen}
            deleteId={deleteId}
            deleteService={AppVersionDeleteService}
            handleSuccess={deletehandleSuccess}
            handleException={deletehandleException}
          />
        </CardContent>
      </Card>
    </>

    // <div style={{
    //   height: '100vh'
    // }}>
    //  <div style={{ height: '100%', width: '100%', paddingRight: 2 }}>
    //     <DataGrid
    //       rows={data}
    //       columns={columns}
    //       pageSize={5}
    //       loading={isLoading}
    //       rowsPerPageOptions={[5]}
    //       disableSelectionOnClick
    //     />
    //  </div>
    // </div>
  );
}

export default AppVersion;
