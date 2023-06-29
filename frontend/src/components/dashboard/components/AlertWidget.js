import { Delete } from '@mui/icons-material';
import {
  Button, Dialog, DialogContent, DialogTitle, TextField, Typography, Stack, Breadcrumbs, Card, CardHeader, CardContent, Chip, Paper
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import React, { useState } from 'react';
import { useUserAccess } from '../../../context/UserAccessProvider';
import { SensorIdAlertUpdate } from '../../../services/LoginPageService';
import ApplicationStore from '../../../utils/localStorageUtil';
import { setAlertPriorityStatus } from '../../../utils/helperFunctions';
import { styled } from '@mui/styles';
/* eslint-disable no-unused-vars */
function AlertWidget({
  dataList, setRefreshData, maxHeight, setAlertList, setNotification, alertOpen
}) {
  const [clearAlert, setClearAlert] = useState(false);
  const [clearAlertReason, setAlertReason] = useState('');
  const [sensorId, setSensorId] = useState('');
  const [errorObject, setErrorObject] = useState({});
  const moduleAccess = useUserAccess()('dashboard');
  const convertDateTime = (value) => {
    const dateSplit = value.split('-');
    const date = `${dateSplit[2]}-${dateSplit[1]}-${dateSplit[0]}`;
    return date;
  };
  const { userDetails } = ApplicationStore().getStorage('userDetails');
  const columns = [
    {
      field: 'a_date',
      headerName: 'Date',
      minwidth: 100,
      flex:1,
      align: 'center',
      headerAlign: 'center',
      renderCell: (params) => (
        <Typography>
          {
            convertDateTime(params.value)
          }
        </Typography>
      ),
    },
    {
      field: 'a_time',
      headerName: 'Time',
      minwidth: 100,
      flex:1,
      align: 'center',
      headerAlign: 'center'
    },
    {
      field: 'sensorTag',
      headerName: 'Sensor/ Device Tag',
      minwidth: 160,
      flex:1,
      align: 'center',
      headerAlign: 'center'
    },
    {
      field: 'value',
      headerName: 'Value',
      minwidth: 100,
      flex:1,
      align: 'center',
      headerAlign: 'center'
    },
   
    {
      field: 'msg',
      headerName: 'Message',
      minwidth: 300,
      flex:1,
      align: 'center',
      headerAlign: 'center'
    },
    // {
    //   field: 'alarmType',
    //   headerName: 'Alarm',
    //   width: 100,
    //   headerAlign: 'center'
    // },
    {
      field: 'alertType',
      headerName: 'Status',
      minWidth: 120,
      maxWidth:250,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      renderCell: (params) => {
        let element = {
          alertLabel: 'Good',
          alertColor: 'green',
        };
    
        element = setAlertPriorityStatus(element, params.row.alertType);
    
        return (
          <Chip
          className='w-[120px] font-[customfont] font-normal text-sm'
            variant="outlined"
            label={element.alertLabel}
            style={{
              fontWeight:'600',
              color: 'white',
              borderColor: element.alertColor,
              background: element.alertColor,
            }}
          />
        );
      },
    },    
    userDetails?.userRole !== 'User' &&
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      width: 150,
      cellClassName: 'actions',
      getActions: (params) => [
        <ClearAlert selectedRow={params.row} />,
      ],
    },
  ];

  function ClearAlert({ selectedRow }) {
    return (
      selectedRow.alarmType === 'Latch'
        ? (
          <Button
          style={{background:'green'}}
          sx={{
            color: 'white',
            background: 'green',
            boxShadow: 'none',
            fontSize: '14px',
            fontWeight: '600',
            letterSpacing: '1px',
            padding:'8px 15px'
          }}
          startIcon={<Delete />}
          onClick={(e) => {
            setSensorId(selectedRow.id);
            setClearAlert(true);
          }}
        >
          Clear
          </Button>
        )
        : ''
    );
  }

  const handleSubmit = (e) => {
    e.preventDefault();

    SensorIdAlertUpdate({
      sensor_id: sensorId, clearAlertReason,
    }, handleSuccess, handleException);

    setClearAlert(false);
    setAlertReason('');
  };

  const handleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setAlertList((oldValue) => oldValue.filter((data) => {
      return data.id !== sensorId;
    }));
    setRefreshData((oldvalue) => !oldvalue);
  };

  /* eslint-disable-next-line */
  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setErrorObject({});
  };
  const Buttons = styled(Button)(
    () => ({
      padding: "10px 25px",
      marginTop: '20px',
      marginBottom: '15px',
      fontSize: '13px',
      borderRadius: '10px',
      fontWeight: '600',
      fontFamily: 'customfont',
      letterSpacing: '1px',
      color: 'white'
    }
    ));
  return (
    <>
      <Card
        className={'w-full h-[45vh] sm:h-[41vh] lg:h-[40vh]'}
        sx={{
          borderRadius: '12px',
          boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px',

        }}
      >
        <Paper elevation={3} className={'h-full '}>
          <CardHeader
            title={
              <Typography
                sx={{
                  fontSize: '25px',
                  fontFamily: 'customfont',
                  fontWeight: '600',
                  color: 'inherit',
                  textAlign: 'left',
                  letterSpacing: '1px',
                  marginLeft: '10px'
                }}
                underline="hover"
              >
                Alerts
              </Typography>
            }
            sx={{ paddingBottom: 0 }}
          />
          <CardContent
            className={'w-full h-[93%] sm:h-[92%]'}
            sx={{
              paddingBottom: '16px',
              borderRadius: '12px',
            }}
          >
            <DataGrid
              className='w-full'
              rows={dataList || []}
              columns={columns}
              pageSize={3}
              rowsPerPageOptions={[3]}
              disableSelectionOnClick
              sx={{
                border: 'none',
              }}
              columnVisibilityModel={{
                actions: moduleAccess.delete
              }}
            />
          </CardContent>
        </Paper>
      </Card>
      <Dialog
        sx={{ '& .MuiDialog-paper': { minWidth: '40%', borderRadius: '12px', padding: '10px' } }}
        maxWidth="sm"
        open={clearAlert}
      >
        <DialogTitle
          className={'color-[#484848] text-center'}
          sx={{
            fontWeight: '600',
            fontSize: '18px',
            fontFamily: 'customfont',
            letterSpacing: '1px'
          }}
        >
          Clear alert with reason
        </DialogTitle>
        <DialogContent>
          <form className="mt-2 space-y-6" onSubmit={handleSubmit}>
            <div
              className="rounded-[12px] -space-y-px"
              sx={{ textAlign: '-webkit-center' }}
            >
              <TextField
                className={'text-center'}
                id="outlined-name"
                label="Reason"
                value={clearAlertReason}
                fullWidth
                required
                multiline
                rows={5}
                onChange={(e) => {
                  setAlertReason(e.target.value);
                }}
                InputLabelProps={{
                  shrink: true,
                }}
              />
              <Stack
                direction="row"
                justifyContent="center"
                alignItems="center"
                textAlign={'center'}
                spacing={3}
                sx={{ marginTop: '20px' }}

              >
                <Buttons type="submit"
                    style={{
                      background: 'rgb(19, 60, 129)',}}
                 >

                  Clear
                </Buttons>
                <Buttons
                  style={{
                    background: 'rgb(19, 60, 129)',}}
                  onClick={() => {
                    setClearAlert(false);
                    setAlertReason('');
                  }}
                >
                  Cancel
                </Buttons>
              </Stack>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </>
  );
}

export default AlertWidget;