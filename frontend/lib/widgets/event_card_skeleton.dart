import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

class EventCardSkeleton extends StatelessWidget {
  const EventCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      child: Shimmer.fromColors(
        baseColor: Colors.grey.shade300,
        highlightColor: Colors.grey.shade100,
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [BoxShadow(color: const Color.fromRGBO(0, 0, 0, 0.04), blurRadius: 8, offset: const Offset(0,4))],
          ),
          height: 140,
          child: Row(
            children: [
              Container(width: 140, height: 140, margin: const EdgeInsets.all(8), decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(8))),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 12.0, horizontal: 8.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(height: 18, width: double.infinity, color: Colors.grey.shade300),
                      const SizedBox(height: 8),
                      Container(height: 14, width: 150, color: Colors.grey.shade300),
                      const Spacer(),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(height: 12, width: 80, color: Colors.grey.shade300),
                          Container(height: 12, width: 60, color: Colors.grey.shade300),
                        ],
                      ),
                    ],
                  ),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}
