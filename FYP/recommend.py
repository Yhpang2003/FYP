import sys
import json
import mysql.connector
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import linear_kernel

# --- Configuration ---
db_config = {
    'user': 'root',
    'password': '',
    'host': 'localhost',
    'database': 'fyp',
    'raise_on_warnings': True
}

def get_hybrid_recommendations(current_product_id, user_id):
    try:
        conn = mysql.connector.connect(**db_config)
        
        # 1. Fetch ALL available products
        query_products = "SELECT product_id, name, description, category_id FROM Product WHERE status = 'Available'"
        df = pd.read_sql(query_products, conn)
        
        # 2. Fetch User History with Activity Type (if logged in)
        user_history = []
        if user_id > 0:
            # We fetch product_id AND activity_type
            query_history = f"""
                SELECT product_id, activity_type 
                FROM Browse_History 
                WHERE user_id = {user_id} 
                ORDER BY viewed_at DESC 
                LIMIT 10
            """
            history_df = pd.read_sql(query_history, conn)
            # Convert to a list of dictionaries: [{'product_id': 1, 'activity_type': 'view'}, ...]
            user_history = history_df.to_dict('records')
            
        conn.close()

        if current_product_id not in df['product_id'].values:
            return []

        # 3. Build Content Matrix (TF-IDF)
        df['content'] = df['name'] + " " + df['description'] + " category_" + df['category_id'].astype(str)
        tfidf = TfidfVectorizer(stop_words='english')
        tfidf_matrix = tfidf.fit_transform(df['content'])
        cosine_sim = linear_kernel(tfidf_matrix, tfidf_matrix)
        
        df = df.reset_index(drop=True)
        indices = pd.Series(df.index, index=df['product_id']).drop_duplicates()

        # --- Helper function to get similar items for a single product ID ---
        def get_sim_scores(pid):
            if pid not in indices: return []
            idx = indices[pid]
            sim_scores = list(enumerate(cosine_sim[idx]))
            return sim_scores

        # 4. Calculate Scores based on CURRENT product
        final_scores = {}
        
        # Weight for the current product being viewed (Highest Priority)
        current_prod_scores = get_sim_scores(current_product_id)
        for i, score in current_prod_scores:
            final_scores[i] = final_scores.get(i, 0) + (score * 2.0) # Base weight for current context

        # 5. Calculate Scores based on USER HISTORY (Weighted by Action)
        if user_history:
            for item in user_history:
                hist_pid = item['product_id']
                action = item['activity_type']
                
                # Determine weight based on action
                if action == 'add_to_cart':
                    weight = 1.5  # High importance
                else: # 'view'
                    weight = 0.5  # Lower importance

                hist_scores = get_sim_scores(hist_pid)
                for i, score in hist_scores:
                    # Add to total score
                    final_scores[i] = final_scores.get(i, 0) + (score * weight)

        # 6. Sort and Format Results
        # Convert dict back to list of tuples for sorting
        sim_scores_list = list(final_scores.items())
        
        # Sort descending
        sim_scores_list = sorted(sim_scores_list, key=lambda x: x[1], reverse=True)
        
        # Get top indices (excluding the current product itself)
        recommended_indices = []
        for i, score in sim_scores_list:
            pid = df['product_id'].iloc[i]
            if pid != current_product_id:
                recommended_indices.append(i)
                if len(recommended_indices) >= 10:
                    break

        return df['product_id'].iloc[recommended_indices].tolist()

    except Exception as e:
        print(f"Error: {e}", file=sys.stderr) 
        return []

if __name__ == "__main__":
    # Default user_id to 0 if not provided
    current_pid = 0
    uid = 0
    
    if len(sys.argv) > 1:
        try:
            current_pid = int(sys.argv[1])
        except ValueError:
            pass
            
    if len(sys.argv) > 2:
        try:
            uid = int(sys.argv[2])
        except ValueError:
            pass

    if current_pid > 0:
        recommendations = get_hybrid_recommendations(current_pid, uid)
        print(json.dumps(recommendations))
    else:
        print("[]")