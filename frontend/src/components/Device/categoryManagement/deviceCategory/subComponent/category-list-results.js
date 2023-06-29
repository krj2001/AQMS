import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/DeleteOutlined';
import CategoryModel from './CategoryModelComponent';
import { CategoryListToolbar } from './CategoryListToolbar';
import { CategoryFetchService, CategoryDeleteService } from '../../../../../services/LoginPageService';
import NotificationBar from '../../../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../../../context/UserAccessProvider';
import DeleteConfirmationDailog from '../../../../../utils/confirmDeletion';
import { Card, CardContent, CardHeader, Divider } from '@mui/material';

export function CategoryListResults() {
  const columns = [
    {
      field: 'categoryName',
      headerName: 'Category Name',
      minWidth: 200,
      align: 'center',
      flex: 1,
      headerAlign: 'center'
    },
    {
      field: 'categoryDescription',
      headerName: 'Category Description',
      minWidth: 300,
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
        <EditData selectedRow={params.row} />, <DeleteData selectedRow={params.row} />,
      ],
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

  const handleException = (errorObject) => {
  };

  useEffect(() => {
    CategoryFetchService(handleSuccess, handleException);
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
        className='mt-0'
        style={{padding:'0 15px'}}
        title={
          <CategoryListToolbar
            setIsAddButton={setIsAddButton}
            setEditCategory={setEditCategory}
            setOpen={setOpen}
            userAccess={moduleAccess}
          />
        }
      />
      <CardContent className={'w-full p-0 h-[450px]'}>
        <DataGrid
          sx={{ border: 'none', fontFamily: 'customfont', color: 'black', letterSpacing: '1px' }}
          rows={CategoryList}
          columns={columns}
          pageSize={5}
          loading={isLoading}
          rowsPerPageOptions={[5]}
          disableSelectionOnClick
        />
        <CategoryModel
          isAddButton={isAddButton}
          categoryData={editCategory}
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
          deleteService={CategoryDeleteService}
          handleSuccess={deletehandleSuccess}
          handleException={deletehandleException}
        />
      </CardContent>
    </Card>
  </>
  );
}
