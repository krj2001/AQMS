import { Delete, Edit } from '@mui/icons-material';
import {
  Box, Container,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import React, { useState, useEffect } from 'react';

import GasCylinderModal from '../components/gasCylinder/GasCylinderModal';
import GasCylinderToolbar from '../components/gasCylinder/GasCylinderToolbar';
import NotificationBar from '../components/notification/ServiceNotificationBar';
import { useUserAccess } from '../context/UserAccessProvider';
import { GasCylinderDeleteService, GasCylinderFetchService } from '../services/LoginPageService';
import DeleteConfirmationDailog from '../utils/confirmDeletion';

function GasCylinder() {
  const [open, setOpen] = useState(false);
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editData, setEditData] = useState({});
  const [gasCylinderList, setGasCylinderList] = useState([]);
  const [isLoading, setGridLoading] = useState(true);
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('gascylinder');

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const columns = [
    {
      field: 'gasCylinderName',
      headerName: 'Gas Cylinder Name',
      width: 170,
    },
    {
      field: 'expiryDate',
      headerName: 'Expiry Date',
      width: 100,
    },
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      width: 100,
      cellClassName: 'actions',

      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];

  useEffect(() => {
    GasCylinderFetchService(handleFetchSuccess, handleFetchException);
  }, [refreshData]);

  const handleFetchSuccess = (dataObject) => {
    setGridLoading(false);
    setGasCylinderList(dataObject.data);
  };

  const handleFetchException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const deletehandleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
      setDeleteId('');
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
      setDeleteId('');
      handleClose();
    }, 3000);
  };
  function EditData(props) {
    return moduleAccess.edit && (
      <Edit
        style={{ cursor: 'pointer' }}
        onClick={(event) => {
          event.stopPropagation();
          setIsAddButton(false);
          setEditData(props.selectedRow);
          setOpen(true);
        }}
      />
    );
  }

  function DeleteData(props) {
    return moduleAccess.delete && (
      <Delete onClick={() => {
        setDeleteId(props.selectedRow.id);
        setDeleteDailogOpen(true);
      }}
      />
    );
  }
  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };
  return (
    <Container maxWidth={false}>
      <Box sx={{
        mt: 1,
      }}
      >
        <GasCylinderToolbar
          moduleAccess={moduleAccess}
          setIsAddButton={setIsAddButton}
          setOpen={setOpen}
        />
        <div style={{ height: 400, width: '100%' }}>
          <DataGrid
            rows={gasCylinderList}
            columns={columns}
            pageSize={5}
            loading={isLoading}
            rowsPerPageOptions={[5]}
            disableSelectionOnClick
          />
        </div>
        <GasCylinderModal
          open={open}
          isAddButton={isAddButton}
          editData={editData}
          setEditData={setEditData}
          setOpen={setOpen}
          setNotification={setNotification}
          setRefreshData={setRefreshData}
        />
      </Box>
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
        deleteService={GasCylinderDeleteService}
        handleSuccess={deletehandleSuccess}
        handleException={deletehandleException}
      />
    </Container>
  );
}

export default GasCylinder;
