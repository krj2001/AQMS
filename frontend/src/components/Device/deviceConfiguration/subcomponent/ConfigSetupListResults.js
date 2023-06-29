import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/DeleteOutlined';
import ConfigSetupModal from './ConfigSetupModalComponent';
import { ConfigSetupListToolbar } from './ConfigSetupListToolbar';
import { ConfigSetupFetchService, ConfigSetupDeleteService } from '../../../../services/LoginPageService';
import NotificationBar from '../../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../../context/UserAccessProvider';
import DeleteConfirmationDailog from '../../../../utils/confirmDeletion';
import { Card, CardContent, CardHeader, Divider } from '@mui/material';

export function ConfigSetupListResults() {
  const columns = [
    {
      field: 'accessPointName',
      headerName: 'Access Point Name',
      minWidth: 170,
      align: 'center',
      flex: 1,
      headerAlign: 'center'
    },
    {
      field: 'ftpAccountName',
      headerName: 'FTP Account Name',
      minWidth: 170,
      align: 'center',
      flex: 1,
      headerAlign: 'center'
    },
    {
      field: 'port',
      headerName: 'Port',
      minWidth: 100,
      align: 'center',
      flex: 1,
      headerAlign: 'center'
    },
    {
      field: 'serverUrl',
      headerName: 'Server Url',
      minWidth: 200,
      align: 'center',
      flex: 1,
      headerAlign: 'center'
    },
    {
      field: 'apn',
      headerName: 'APN',
      minWidth: 100,
      align: 'center',
      flex: 1,
      headerAlign: 'center'
    },
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      minWidth: 100,
      align: 'center',
      flex: 1,
      cellClassName: 'actions',
      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];

  const [open, setOpen] = useState(false);
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editConfigSetup, setEditConfigSetup] = useState([]);
  const [configSetupList, setConfigSetupList] = useState([]);
  const [isLoading, setGridLoading] = useState(true);
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('device');

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const handleSuccess = (dataObject) => {
    setGridLoading(false);
    setConfigSetupList(dataObject?.data || []);
  };

  const handleException = (errorObject) => {
    
  };

  function DeleteData(props) {
    return moduleAccess.delete && (
      <DeleteIcon onClick={() => {
        setDeleteId(props.selectedRow.id);
        setDeleteDailogOpen(true);
      }}
      />
    );
  }

  useEffect(() => {
    ConfigSetupFetchService(handleSuccess, handleException);
  }, [refreshData]);

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

  function EditData(props) {
    return (moduleAccess.edit
      && (
        <EditIcon onClick={(event) => {
          event.stopPropagation();
          setIsAddButton(false);
          setEditConfigSetup(props.selectedRow);
          setOpen(true);
        }}
        />
      ));
  }

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  return (
    <>
      <Card style={{ boxShadow: 'none' }}>
        <CardHeader
          className='mt-0' 
          style={{padding:'0 15px'}}
          title={
            <ConfigSetupListToolbar
              setIsAddButton={setIsAddButton}
              setEditConfigSetup={setEditConfigSetup}
              setOpen={setOpen}
              editConfigSetup={editConfigSetup}
              userAccess={moduleAccess}
            />
          }
        />
        <CardContent className={'w-full h-[56vh]'}
        >
          <DataGrid
            sx={{ border: 'none', fontFamily: 'customfont', fontSize: '16px', fontWeight: '500', color: 'black', letterSpacing: '1px' }}
            rows={configSetupList}
            columns={columns}
            pageSize={5}
            loading={isLoading}
            rowsPerPageOptions={[5]}
            disableSelectionOnClick
          />
          <ConfigSetupModal
            isAddButton={isAddButton}
            configSetupData={editConfigSetup}
            open={open}
            setOpen={setOpen}
            setRefreshData={setRefreshData}
            handleClose={handleClose}
            openNotification={openNotification}
            setNotification={setNotification}
          />
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
            deleteService={ConfigSetupDeleteService}
            handleSuccess={deletehandleSuccess}
            handleException={deletehandleException}
          />
        </CardContent>
      </Card>
    </>
  );
}
