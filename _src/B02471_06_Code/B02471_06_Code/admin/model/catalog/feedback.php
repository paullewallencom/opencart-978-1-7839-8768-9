<?php

class ModelCatalogfeedback extends Model {

    public function addFeedback($data) {
        $this->event->trigger('pre.admin.feedback.add',$data);
        $this->db->query("INSERT INTO " . DB_PREFIX . "feedback SET status = '" . (int) $data['status'] . "'");
        $feedback_id = $this->db->getLastId();
        foreach ($data['feedback_description'] as $language_id =>$value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "feedback_description SET feedback_id = '" . (int) $feedback_id . "', language_id = '" . (int) $language_id . "', author = '" . $this->db->escape($value['author']) . "', description = '" . $this->db->escape($value['description']) . "'");
        }
        if (isset($data['feedback_store'])) {
            foreach ($data['feedback_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "feedback_to_store SET feedback_id = '" . (int) $feedback_id . "', store_id = '" . (int) $store_id . "'");
            }
        }
        if (isset($data['feedback_layout'])) {
            foreach ($data['feedback_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "feedback_to_layout SET feedback_id = '" . (int) $feedback_id . "', store_id = '" . (int) $store_id . "', layout_id = '" . (int) $layout_id . "'");
            }
        }
        $this->event->trigger('post.admin.feedback.add',$feedback_id);
        return $feedback_id;
    }

    /*
      The codes above show how we can query to the database. We have to start with the $this->db->query() and inside the braces we write the sql query which we have already known at the global methods on the second chapter. According to the above code it inserts feedback id, sort order and status on the feedback table and then retrieves the latest inserted feedback id and assigned to the feedback_id and the description is looped as you will get the description as array because it can consists of many languages. Thus it inserts into the description table with feedback id, language id and the feedback description as well. As OpenCart supports the multi-store and multiple layouts so you must take care of them. So after the insertion of the description, we have run the store query to insert the store then the layout insertion. Then a cache is deleted if it was created already */

    public function editFeedback($feedback_id,$data) {
        $this->event->trigger('pre.admin.feedback.edit',
                $data);
        $this->db->query("UPDATE " . DB_PREFIX . "feedback SET status = '" . (int) $data['status'] . "' WHERE feedback_id = '" . (int) $feedback_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "feedback_description WHERE feedback_id = '" . (int) $feedback_id . "'");
        foreach ($data['feedback_description'] as
                $language_id =>
                $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "feedback_description SET feedback_id = '" . (int) $feedback_id . "', language_id = '" . (int) $language_id . "', author = '" . $this->db->escape($value['author']) . "', description = '" . $this->db->escape($value['description']) . "'");
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "feedback_to_store WHERE feedback_id = '" . (int) $feedback_id . "'");

        if (isset($data['feedback_store'])) {
            foreach ($data['feedback_store'] as
                    $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "feedback_to_store SET feedback_id = '" . (int) $feedback_id . "', store_id = '" . (int) $store_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "feedback_to_layout WHERE feedback_id = '" . (int) $feedback_id . "'");

        if (isset($data['feedback_layout'])) {
            foreach ($data['feedback_layout'] as
                    $store_id =>
                    $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "feedback_to_layout SET feedback_id = '" . (int) $feedback_id . "', store_id = '" . (int) $store_id . "', layout_id = '" . (int) $layout_id . "'");
            }
        }

        $this->event->trigger('post.admin.feedback.edit', $feedback_id);
    }

    /*
      The queries update the database table row of feedback, feedback description, feedback store, and feedback layout. The first query shown at the code will update the feedback table row but for other tables of feedback description, feedback store and feedback layout, first it deletes all the related feedback as per the feedback id and then insert them again. When feedback table is updated then it deletes all the related feedback description at the feedback_description table and then inserted the updated data, although no changes are made, it takes them as the new value and insert on loop. Same is done for the feedback to layout and feedback to store. Then it deletes the cache if it is already created. */

    public function deleteFeedback($feedback_id) {
        $this->event->trigger('pre.admin.feedback.delete',$feedback_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "feedback WHERE feedback_id = '" . (int) $feedback_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "feedback_description WHERE feedback_id = '" . (int) $feedback_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "feedback_to_store WHERE feedback_id = '" . (int) $feedback_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "feedback_to_layout WHERE feedback_id = '" . (int) $feedback_id . "'");

        $this->event->trigger('post.admin.feedback.delete',$feedback_id);
    }

    /*
      Codes above is used to delete the feedback, you have to take care to delete from all the tables whenever you operate the delete operation. As per our feedback you have to delete from the feedback, feedback description and feedback to store and feedback to layout as well as the cache file. */

    public function getfeedback($feedback_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "feedback WHERE feedback_id = '" . (int) $feedback_id . "'");
        return $query->row;
    }

    /*
      The snippets of code is used to retrieve a row, to run select query you have to run the query with the $this->db->query() and then assign to some variable and run with $Variable_Name->row; to retrieve a single column and to retrieve the multiple rows we have to write   $Variable_Name->rows; which returns an array. As per our SQL query we just need a single row of the specified feedback id so we have performed the $query->row; */

    public function getFeedbacks($data = array()) {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "feedback f LEFT JOIN " . DB_PREFIX . "feedback_description fd ON (f.feedback_id = fd.feedback_id) WHERE fd.language_id = '" . (int) $this->config->get('config_language_id') . "' LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
            $query = $this->db->query($sql);
            return $query->rows;
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "feedback f LEFT JOIN " . DB_PREFIX . "feedback_description id ON (f.feedback_id = fd.feedback_id) WHERE fd.language_id = '" . (int) $this->config->get('config_language_id') . "' ORDER BY f.date_added DESC");
            $feedback_data= $query->rows;
            return $feedback_data;
        }
    }

    /*
      Retrieving all the feedback from the database is done with the above code. If $data is passed, means if there are sort order style, limit of rows retrieve then you need to filter the data by them from SQL query and retrieve the required rows only else it will show from the cache file. It will retrieve data from the feedback, feedback description table and returned as an array. It is sorted by passed data as name or so on else by default it is sorted by the feedback id, then limitation to retrieve the rows are done and run the query and retrieve the rows. */

    public function getFeedbackDescriptions($feedback_id) {
        $feedback_description_data= array();
        $query= $this->db->query("SELECT * FROM " . DB_PREFIX . "feedback_description WHERE feedback_id = '" . (int) $feedback_id . "'");
        foreach ($query->rows as $result) {
            $feedback_description_data[$result['language_id']]= array(
                'author' => $result['author'],
                'description' => $result['description']
            );
        }
        return $feedback_description_data;
    }

    /*
      Above code retrieve description of the respective feedback id passed and it will return all the languages’ description and return the description in an array.
     */

    public function getFeedbackStores($feedback_id) {
        $feedback_store_data = array();
        $query= $this->db->query("SELECT * FROM " . DB_PREFIX . "feedback_to_store WHERE feedback_id = '" . (int) $feedback_id . "'");

        foreach ($query->rows as $result) {
            $feedback_store_data[]= $result['store_id'];
        }
        return $feedback_store_data;
    }

    /* This code returns all stores that the specified feedback id passed. */

    public function getFeedbackLayouts($feedback_id) {
        $feedback_layout_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "feedback_to_layout WHERE feedback_id = '" . (int) $feedback_id . "'");

        foreach ($query->rows as $result) {
            $feedback_layout_data[$result['store_id']] = $result['layout_id'];
        }
        return $feedback_layout_data;
    }

    /* Above codes returns all the layouts of the specified feedback id passed. */

    public function getTotalFeedbacks() {
        $query= $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "feedback");
        return $query->row['total'];
    }

    /* It returns the total number of feedbacks. */

    public function getTotalFeedbacksByLayoutId($layout_id) {
        $query= $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "feedback_to_layout WHERE layout_id = '" . (int) $layout_id . "'");
        return $query->row['total'];
    }

}

/*
The function getTotalfeedbacksByLayout will return number of feedback counts that has the layout id passed and it closed the main model class. Like this way you can create model file and can make any kinds of data retrieval, insertion, deletion works and same will be used on the controller files by loading the model file.
*/


