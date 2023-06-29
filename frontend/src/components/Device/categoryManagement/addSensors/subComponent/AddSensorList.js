import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/DeleteOutlined';
import { SensorListFetchService, SensorDeleteService } from '../../../../../services/LoginPageService';
import { AddSensorCategoryToolbar } from './AddSensorCategoryToolbar';
import AddSensorModal from './AddSensorModal';
import NotificationBar from '../../../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../../../context/UserAccessProvider';
import DeleteConfirmationDailog from '../../../../../utils/confirmDeletion';
import { Card, CardContent, CardHeader, Divider } from '@mui/material';

export function AddSensorList() {
  const columns = [
    {
      field: 'sensorName',
      headerName: 'Sensor Category',
      minWidth: 200,
      align: 'center',
      flex : 1,
      headerAlign: 'center'
    },
    {
      field: 'sensorOutput',
      headerName: 'Sensor Output',
      minWidth: 300,
      align: 'center',
      flex : 1,
      headerAlign: 'center'
    },
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      minWidth: 100,
      align: 'center',
      flex : 1,
      cellClassName: 'actions',
      getActions: (params) => {
        return [
          <EditData selectedRow={params.row} />,
          // <SetAlarm selectedRow={params.row}/>,
          <DeleteData selectedRow={params.row} />,
        ];
      },
    },
  ];

  const [open, setOpen] = useState(false);
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editCategory, setEditCategory] = useState([]);
  const [CategoryList, setCategoryList] = useState([]);
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
    setCategoryList(dataObject.data);
  };

  const handleException = () => {
  };

  useEffect(() => {
    SensorListFetchService(handleSuccess, handleException);
  }, [refreshData]);

  function EditData(props) {
    return (moduleAccess.edit
        && (
          <EditIcon
            style={{ cursor: 'pointer' }}
            onClick={(event) => {
              event.stopPropagation();
              setIsAddButton(false);
              setEditCategory(props.selectedRow);
              setOpen(true);
            }}
          />
        ));
  }

  function DeleteData(props) {
    return moduleAccess.delete && (
      <DeleteIcon
        style={{ cursor: 'pointer' }}
        onClick={() => {
          setDeleteId(props.selectedRow.id);
          setDeleteDailogOpen(true);
        }}
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
      style={{padding:'0 20px'}}
        title={
          <AddSensorCategoryToolbar
            setIsAddButton={setIsAddButton}
            setEditCategory={setEditCategory}
            setOpen={setOpen}
            userAccess={moduleAccess}
          />
        }
      />
      <CardContent className={'w-full p-0 h-[450px]'}>
        <DataGrid
          sx={{ border: 'none', fontFamily: 'customfont', color: 'black' }}
          rows={CategoryList}
          columns={columns}
          pageSize={5}
          loading={isLoading}
          rowsPerPageOptions={[5]}
          disableSelectionOnClick
        />
        <AddSensorModal
          isAddButton={isAddButton}
          categoryData={editCategory}
          open={open}
          setOpen={setOpen}
          CategoryList={CategoryList}
          setRefreshData={setRefreshData}
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
          deleteService={SensorDeleteService}
          handleSuccess={deletehandleSuccess}
          handleException={deletehandleException}
        />
      </CardContent>
    </Card>
  </>
  );
}
